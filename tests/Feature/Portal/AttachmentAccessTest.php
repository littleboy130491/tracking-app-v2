<?php

/**
 * File: tests/Feature/Portal/AttachmentAccessTest.php
 * Responsibility: Guards the authorized /attachments/{id} serving route.
 * What it does:
 * - Proves guests are sent to login, customers only fetch portal-visible files
 *   of their own companies' published shipments, and staff (admin, operator)
 *   fetch files within their company scope.
 * - Proves non-image files download instead of rendering inline.
 * How to use: `php artisan test --filter=AttachmentAccessTest`.
 * How to extend: add a case when a new attachment owner appears.
 */

namespace Tests\Feature\Portal;

use App\Enums\AttachmentCategory;
use App\Enums\ImportMilestone;
use App\Enums\ShipmentStatus;
use App\Models\Attachment;
use App\Models\Company;
use App\Models\ImportShipment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachmentAccessTest extends TestCase
{
    use RefreshDatabase;

    /** A real 1x1 PNG, so the served content type is detected as an image. */
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        Storage::fake('local');
    }

    public function test_guests_are_sent_to_the_portal_login(): void
    {
        $attachment = $this->attachment($this->shipmentFor($this->company('NUS')));

        $this->get(route('attachments.show', $attachment))
            ->assertRedirect(route('customer.login'));
    }

    public function test_a_customer_can_fetch_a_visible_attachment_of_their_company(): void
    {
        $company = $this->company('NUS');
        $attachment = $this->attachment($this->shipmentFor($company));

        $response = $this->actingAs($this->customerFor($company))
            ->get(route('attachments.show', $attachment))
            ->assertOk()
            ->assertHeader('content-type', 'image/png')
            ->assertHeader('x-content-type-options', 'nosniff')
            // Symfony normalises the Cache-Control directive order.
            ->assertHeader('cache-control', 'max-age=300, private');

        $this->assertSame(base64_decode(self::PNG), $response->streamedContent());
    }

    public function test_a_customer_cannot_fetch_another_companys_attachment(): void
    {
        $attachment = $this->attachment($this->shipmentFor($this->company('JRD')));

        $this->actingAs($this->customerFor($this->company('NUS')))
            ->get(route('attachments.show', $attachment))
            ->assertForbidden();
    }

    public function test_a_customer_cannot_fetch_an_internal_only_attachment(): void
    {
        $company = $this->company('NUS');
        $attachment = $this->attachment($this->shipmentFor($company), isCustomerVisible: false);

        $this->actingAs($this->customerFor($company))
            ->get(route('attachments.show', $attachment))
            ->assertForbidden();
    }

    public function test_a_customer_cannot_fetch_an_attachment_of_a_draft_shipment(): void
    {
        $company = $this->company('NUS');
        $attachment = $this->attachment($this->shipmentFor($company, ShipmentStatus::Draft));

        $this->actingAs($this->customerFor($company))
            ->get(route('attachments.show', $attachment))
            ->assertForbidden();
    }

    public function test_staff_can_fetch_internal_files_of_any_company(): void
    {
        $attachment = $this->attachment($this->shipmentFor($this->company('JRD')), isCustomerVisible: false);

        $this->actingAs(User::query()->where('email', 'admin@example.com')->firstOrFail())
            ->get(route('attachments.show', $attachment))
            ->assertOk();
    }

    public function test_an_operator_stays_inside_the_assigned_companies(): void
    {
        // The seeded operator is linked to NUS and SNI only.
        $operator = User::query()->where('email', 'operator@example.com')->firstOrFail();

        $this->actingAs($operator)
            ->get(route('attachments.show', $this->attachment($this->shipmentFor($this->company('NUS')), isCustomerVisible: false)))
            ->assertOk();

        $this->actingAs($operator)
            ->get(route('attachments.show', $this->attachment($this->shipmentFor($this->company('JRD')))))
            ->assertForbidden();
    }

    public function test_documents_download_instead_of_rendering_inline(): void
    {
        $company = $this->company('NUS');
        $attachment = $this->attachment($this->shipmentFor($company), type: 'application/pdf', ext: 'pdf');

        $response = $this->actingAs($this->customerFor($company))
            ->get(route('attachments.show', $attachment))
            ->assertOk();

        $this->assertStringContainsString(
            'attachment',
            (string) $response->headers->get('content-disposition'),
        );
    }

    public function test_a_missing_file_is_a_not_found(): void
    {
        $company = $this->company('NUS');
        $attachment = $this->attachment($this->shipmentFor($company));
        Storage::disk('local')->delete($attachment->path);

        $this->actingAs($this->customerFor($company))
            ->get(route('attachments.show', $attachment))
            ->assertNotFound();
    }

    public function test_a_thumbnail_request_returns_a_resized_webp(): void
    {
        $company = $this->company('NUS');
        $attachment = $this->attachment($this->shipmentFor($company), bytes: $this->imageBytes(800, 600));

        $response = $this->actingAs($this->customerFor($company))
            ->get(route('attachments.show', ['attachment' => $attachment, 'size' => 'thumb']))
            ->assertOk()
            ->assertHeader('content-type', 'image/webp');

        $this->assertSame([200, 200], $this->dimensions((string) $response->getContent()));
    }

    public function test_the_medium_size_fills_its_box(): void
    {
        $company = $this->company('NUS');
        $attachment = $this->attachment($this->shipmentFor($company), bytes: $this->imageBytes(800, 600));

        $response = $this->actingAs($this->customerFor($company))
            ->get(route('attachments.show', ['attachment' => $attachment, 'size' => 'medium']))
            ->assertOk();

        $this->assertSame([640, 640], $this->dimensions((string) $response->getContent()));
    }

    public function test_the_large_size_never_upscales(): void
    {
        $company = $this->company('NUS');
        $attachment = $this->attachment($this->shipmentFor($company), bytes: $this->imageBytes(800, 600));

        $response = $this->actingAs($this->customerFor($company))
            ->get(route('attachments.show', ['attachment' => $attachment, 'size' => 'large']))
            ->assertOk();

        $this->assertSame([800, 600], $this->dimensions((string) $response->getContent()));
    }

    public function test_a_document_ignores_the_size_parameter(): void
    {
        $company = $this->company('NUS');
        $attachment = $this->attachment($this->shipmentFor($company), type: 'application/pdf', ext: 'pdf');

        $response = $this->actingAs($this->customerFor($company))
            ->get(route('attachments.show', ['attachment' => $attachment, 'size' => 'thumb']))
            ->assertOk();

        $this->assertStringContainsString('attachment', (string) $response->headers->get('content-disposition'));
        $this->assertNotSame('image/webp', $response->headers->get('content-type'));
    }

    public function test_an_unknown_size_serves_the_original(): void
    {
        $company = $this->company('NUS');
        $attachment = $this->attachment($this->shipmentFor($company));

        $response = $this->actingAs($this->customerFor($company))
            ->get(route('attachments.show', ['attachment' => $attachment, 'size' => 'huge']))
            ->assertOk()
            ->assertHeader('content-type', 'image/png');

        $this->assertSame(base64_decode(self::PNG), $response->streamedContent());
    }

    public function test_authorization_still_applies_to_thumbnail_requests(): void
    {
        $attachment = $this->attachment($this->shipmentFor($this->company('JRD')));

        $this->actingAs($this->customerFor($this->company('NUS')))
            ->get(route('attachments.show', ['attachment' => $attachment, 'size' => 'thumb']))
            ->assertForbidden();
    }

    private function company(string $code): Company
    {
        return Company::query()->where('code', $code)->firstOrFail();
    }

    private function customerFor(Company $company): User
    {
        $user = User::factory()->create(['name' => 'Portal Customer']);
        $user->assignRole(Role::CUSTOMER);
        $user->companies()->attach($company);

        return $user;
    }

    private function shipmentFor(Company $company, ShipmentStatus $status = ShipmentStatus::InProgress): ImportShipment
    {
        return ImportShipment::query()->create([
            'bl_number' => 'BL-ATTACH-'.uniqid(),
            'company_id' => $company->getKey(),
            'company_name_snapshot' => $company->name,
            'current_milestone' => ImportMilestone::DocumentReceived,
            'status' => $status,
            'confirmation_checklist' => false,
        ]);
    }

    private function attachment(
        ImportShipment $shipment,
        bool $isCustomerVisible = true,
        string $type = 'image/png',
        string $ext = 'png',
        ?string $bytes = null,
    ): Attachment {
        $path = 'attachments/'.uniqid('file-').'.'.$ext;
        Storage::disk('local')->put($path, $bytes ?? base64_decode(self::PNG));

        return Attachment::query()->create([
            'disk' => 'local',
            'directory' => 'attachments',
            'visibility' => 'private',
            'name' => 'attachment',
            'path' => $path,
            'type' => $type,
            'ext' => $ext,
            'import_shipment_id' => $shipment->getKey(),
            'category' => AttachmentCategory::SupportingDocument->value,
            'is_customer_visible' => $isCustomerVisible,
        ]);
    }

    /**
     * A flat-colour PNG of the given size, so resize assertions are exact.
     */
    private function imageBytes(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        imagefilledrectangle($image, 0, 0, $width, $height, imagecolorallocate($image, 203, 213, 225));

        ob_start();
        imagepng($image);
        $bytes = (string) ob_get_clean();
        imagedestroy($image);

        return $bytes;
    }

    /**
     * @return array{int, int}
     */
    private function dimensions(string $bytes): array
    {
        $size = getimagesizefromstring($bytes);

        return [(int) $size[0], (int) $size[1]];
    }
}
