<?php

/**
 * File: tests/Feature/Portal/PortalTest.php
 * Responsibility: Verifies the customer portal behaviour from spec.md.
 * What it does:
 * - Covers login routing, the no-registration OTP rule, the signed verify step
 *   (including posting through the rendered form), the attempt rate limiter,
 *   the Export/Import dashboard tabs, per-customer scoping, shipment/container
 *   search, latest journey columns and the import PIB confirmation.
 * - Also asserts the seeded many-to-many between companies and portal users.
 * How to use: `php artisan test --filter=PortalTest`.
 * How to extend: add a test per new portal screen or rule.
 */

namespace Tests\Feature\Portal;

use App\Enums\DraftPibConfirmationStatus;
use App\Enums\ExportMilestone;
use App\Enums\ImportMilestone;
use App\Livewire\Customer\Dashboard;
use App\Livewire\Customer\ExportShipmentDetail;
use App\Livewire\Customer\ImportShipmentDetail;
use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\ExportShipment;
use App\Models\ImportShipment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_the_root_url_sends_guests_to_the_portal_login(): void
    {
        $this->get('/')->assertRedirect(route('customer.login'));
    }

    public function test_guests_cannot_open_the_portal(): void
    {
        $this->get(route('customer.dashboard'))->assertRedirect(route('customer.login'));
    }

    public function test_a_signed_in_admin_lands_on_the_admin_panel_without_a_redirect_loop(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        // Regression: "/" used to send every authenticated user to the portal
        // login, which the guest middleware bounced back to "/" forever
        // (ERR_TOO_MANY_REDIRECTS).
        $this->actingAs($admin)->get('/')->assertRedirect('/admin');
    }

    public function test_an_operator_is_also_sent_to_the_admin_panel(): void
    {
        $operator = User::query()->where('email', 'operator@example.com')->firstOrFail();

        $this->actingAs($operator)->get('/')->assertRedirect('/admin');
    }

    public function test_a_signed_in_user_opening_the_login_screen_is_not_bounced_back_to_it(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('customer.login'))
            ->assertRedirect(route('home'));
    }

    public function test_a_signed_in_customer_lands_on_the_portal(): void
    {
        $customer = User::query()->where('email', 'customer@example.com')->firstOrFail();

        $this->actingAs($customer)->get('/')->assertRedirect(route('customer.dashboard'));
    }

    public function test_an_unknown_email_cannot_request_a_code_and_no_user_is_created(): void
    {
        $before = User::query()->count();

        $this->post(route('customer.login.send'), ['email' => 'nobody@example.com'])
            ->assertSessionHasErrors('email');

        $this->assertSame($before, User::query()->count());
    }

    public function test_an_internal_user_cannot_use_the_portal_login(): void
    {
        $this->post(route('customer.login.send'), ['email' => 'operator@example.com'])
            ->assertSessionHasErrors('email');
    }

    public function test_an_admin_can_request_a_portal_code(): void
    {
        $this->post(route('customer.login.send'), ['email' => 'admin@example.com'])
            ->assertRedirect();
    }

    public function test_a_registered_customer_receives_a_signed_verify_link(): void
    {
        $customer = $this->portalUser();

        $response = $this->post(route('customer.login.send'), ['email' => $customer->email]);

        $response->assertRedirect();

        $location = $response->headers->get('Location');

        $this->assertStringContainsString('/login/verify/', (string) $location);
        $this->assertStringContainsString('signature=', (string) $location);

        $this->get($location)->assertOk()->assertSee('Enter your one-time code');
    }

    public function test_an_expired_or_unsigned_verify_link_returns_a_friendly_error(): void
    {
        // Previously the `signed` middleware answered with a bare 403 page.
        $this->get('/login/verify/not-a-real-id')
            ->assertRedirect(route('customer.login'))
            ->assertSessionHasErrors('email');
    }

    public function test_the_verify_form_posts_back_to_the_signed_url(): void
    {
        $form = $this->openVerifyForm($this->portalUser());

        $this->assertStringContainsString('/login/verify/', $form['action']);
        $this->assertStringContainsString('signature=', $form['action']);
        $this->assertNotSame('', $form['sessionId']);
    }

    public function test_the_correct_code_signs_the_customer_in(): void
    {
        $user = $this->portalUser();
        $form = $this->openVerifyForm($user);

        $this->assertNotSame('', $form['code'], 'The development code should be visible on the verify screen.');

        $this->post($form['action'], ['code' => $form['code'], 'sessionId' => $form['sessionId']])
            ->assertRedirect(route('customer.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_wrong_code_through_the_rendered_form_shows_a_friendly_error(): void
    {
        $form = $this->openVerifyForm($this->portalUser());

        $response = $this->post($form['action'], ['code' => 'WRONGCODE1', 'sessionId' => $form['sessionId']]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('code');

        // Reaching the code check proves the signature and session lock passed.
        $this->assertStringContainsString('invalid', (string) session('errors')->first('code'));
        $this->assertGuest();
    }

    public function test_repeated_wrong_codes_are_rate_limited(): void
    {
        config(['otpz.max_attempts' => 3]);

        $form = $this->openVerifyForm($this->portalUser());

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $this->post($form['action'], ['code' => 'WRONGCODE'.$attempt, 'sessionId' => $form['sessionId']])
                ->assertSessionHasErrors('code');
        }

        $this->post($form['action'], ['code' => 'WRONGCODE4', 'sessionId' => $form['sessionId']])
            ->assertSessionHasErrors('code');

        $this->assertStringContainsString('Too many incorrect codes', (string) session('errors')->first('code'));
        $this->assertGuest();
    }

    public function test_the_attempt_limit_defaults_to_eight(): void
    {
        $this->assertSame(8, config('otpz.max_attempts'));
    }

    public function test_seeded_companies_and_users_are_many_to_many(): void
    {
        $dewi = User::query()->where('email', 'customer@example.com')->firstOrFail();
        $this->assertSame(3, $dewi->companies()->count(), 'A user should be able to manage several companies.');

        $javaRetail = Company::query()->where('code', 'JRD')->firstOrFail();
        $this->assertSame(3, $javaRetail->customers()->count(), 'A company should be able to have several customers.');

        $nusantara = Company::query()->where('code', 'NUS')->firstOrFail();
        $this->assertSame(2, $nusantara->customers()->count());

        // The seeded operator covers NUS and SNI for the admin panel scope.
        $operator = User::query()->where('email', 'operator@example.com')->firstOrFail();
        $this->assertSame(2, $operator->companies()->count());
        $this->assertSame(0, $nusantara->operators()->whereKeyNot($operator->getKey())->count());

        $this->assertSame(5, Company::query()->count());
        $this->assertSame(5, User::query()->role(Role::CUSTOMER)->count());
    }

    public function test_a_user_with_several_companies_sees_all_of_their_export_shipments(): void
    {
        $dewi = User::query()->where('email', 'customer@example.com')->firstOrFail();

        $this->actingAs($dewi)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('BL-EXP-0001')  // PT Nusantara Ekspor
            ->assertSee('BL-EXP-0002'); // CV Borneo Jaya Mandiri
    }

    public function test_the_dashboard_type_filter_switches_between_export_and_import(): void
    {
        $dewi = User::query()->where('email', 'customer@example.com')->firstOrFail();

        $this->actingAs($dewi);

        Livewire::test(Dashboard::class)
            ->assertSee('Type')
            ->assertSet('type', 'export')
            ->assertSee('BL-EXP-0001')
            ->assertDontSee('BL-IMP-0001')
            ->set('type', 'import')
            ->assertSee('BL-IMP-0001')  // PT Sinar Impor
            ->assertDontSee('BL-EXP-0001');
    }

    public function test_a_user_only_sees_shipments_of_the_companies_they_manage(): void
    {
        // Rina manages NUS and JRD, not SIN or BJM.
        $rina = User::query()->where('email', 'rina@nusantara.test')->firstOrFail();

        $this->actingAs($rina);

        Livewire::test(Dashboard::class)
            ->assertSee('BL-EXP-0001')
            ->assertDontSee('BL-EXP-0002')
            ->set('type', 'import')
            ->assertSee('BL-IMP-0002')  // JRD
            ->assertDontSee('BL-IMP-0001'); // SIN
    }

    public function test_the_dashboard_lists_the_company_of_each_shipment(): void
    {
        // Dewi manages NUS, SIN and BJM.
        $dewi = User::query()->where('email', 'customer@example.com')->firstOrFail();

        $this->actingAs($dewi);

        Livewire::test(Dashboard::class)
            ->assertOk()
            ->assertSee('PT Nusantara Ekspor')
            ->assertSee('CV Borneo Jaya Mandiri')
            ->assertSee('BL-EXP-0002')
            ->set('type', 'import')
            ->assertSee('PT Sinar Impor');
    }

    public function test_a_user_can_filter_their_shipments_by_company(): void
    {
        $dewi = User::query()->where('email', 'customer@example.com')->firstOrFail();
        $sinar = Company::query()->where('code', 'SIN')->firstOrFail();

        $this->actingAs($dewi);

        Livewire::test(Dashboard::class)
            ->set('type', 'import')
            ->set('company', (string) $sinar->getKey())
            ->assertSee('BL-IMP-0001')
            ->assertDontSee('BL-IMP-0002');
    }

    public function test_the_number_search_matches_containers_and_shows_the_latest_journey(): void
    {
        // Agus manages SNI, which owns the completed export BL-EXP-0003.
        $agus = User::query()->where('email', 'agus@borneo.test')->firstOrFail();

        $this->actingAs($agus);

        Livewire::test(Dashboard::class)
            ->set('number', 'EGHU6677881')
            ->assertSee('BL-EXP-0003')
            ->assertDontSee('BL-EXP-0001')
            ->assertSee('Latest place')
            ->assertSee('Vessel arrival at port of discharge');
    }

    public function test_the_company_filter_cannot_reveal_another_companys_shipments(): void
    {
        // Rina manages NUS and JRD; SIN is not hers, even when asked for by id.
        $rina = User::query()->where('email', 'rina@nusantara.test')->firstOrFail();
        $sinar = Company::query()->where('code', 'SIN')->firstOrFail();

        $this->actingAs($rina);

        Livewire::test(Dashboard::class)
            ->set('type', 'import')
            ->set('company', (string) $sinar->getKey())
            ->assertDontSee('BL-IMP-0001')
            ->assertSee('No shipments match your filters');
    }

    public function test_clearing_the_filters_restores_every_shipment(): void
    {
        $dewi = User::query()->where('email', 'customer@example.com')->firstOrFail();
        $borneo = Company::query()->where('code', 'BJM')->firstOrFail();

        $this->actingAs($dewi);

        Livewire::test(Dashboard::class)
            ->set('company', (string) $borneo->getKey())
            ->assertDontSee('BL-EXP-0001')
            ->call('clearFilters')
            ->assertSet('company', '')
            ->assertSee('BL-EXP-0001');
    }

    public function test_an_admin_sees_every_shipment_in_the_portal(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(Dashboard::class)
            ->assertSee('BL-EXP-0001')
            ->assertSee('BL-EXP-0002')
            ->assertSee('BL-EXP-0003')
            ->set('type', 'import')
            ->assertSee('BL-IMP-0001')
            ->assertSee('BL-IMP-0002');
    }

    public function test_an_admin_may_open_any_customers_shipment_and_container(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $export = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();
        $exportContainer = $export->containers()->firstOrFail();
        $import = ImportShipment::query()->where('bl_number', 'BL-IMP-0001')->firstOrFail();
        $importContainer = $import->containers()->firstOrFail();

        $this->actingAs($admin)
            ->get(route('customer.export-shipments.show', ['exportShipment' => $export]))
            ->assertOk()
            ->assertSee('BL-EXP-0001');

        $this->actingAs($admin)
            ->get(route('customer.import-shipments.show', ['importShipment' => $import]))
            ->assertOk()
            ->assertSee('BL-IMP-0001');

        $this->actingAs($admin)
            ->get(route('customer.export-containers.show', ['exportContainer' => $exportContainer]))
            ->assertOk()
            ->assertSee($exportContainer->container_number);

        $this->actingAs($admin)
            ->get(route('customer.import-containers.show', ['importContainer' => $importContainer]))
            ->assertOk()
            ->assertSee($importContainer->container_number);
    }

    public function test_a_customer_sees_only_their_own_shipments(): void
    {
        $mine = $this->portalUser();
        $theirs = $this->portalUser('other@example.com', 'Other Trading');

        $ownShipment = $this->exportShipmentFor($mine->companies()->first(), 'BL-MINE-1');
        $otherShipment = $this->exportShipmentFor($theirs->companies()->first(), 'BL-THEIRS-1');

        $this->actingAs($mine)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('BL-MINE-1')
            ->assertDontSee('BL-THEIRS-1');

        // Guessing another company's shipment id must 404.
        $this->actingAs($mine)
            ->get(route('customer.export-shipments.show', ['exportShipment' => $otherShipment->getKey()]))
            ->assertNotFound();

        $this->actingAs($mine)
            ->get(route('customer.export-shipments.show', ['exportShipment' => $ownShipment->getKey()]))
            ->assertOk();
    }

    public function test_a_container_page_is_scoped_to_the_customers_shipments(): void
    {
        $mine = $this->portalUser();
        $theirs = $this->portalUser('other@example.com', 'Other Trading');

        $ownShipment = $this->exportShipmentFor($mine->companies()->first(), 'BL-MINE-2');
        $otherShipment = $this->exportShipmentFor($theirs->companies()->first(), 'BL-THEIRS-2');

        $ownContainer = $ownShipment->containers()->firstOrFail();
        $otherContainer = $otherShipment->containers()->firstOrFail();

        $this->actingAs($mine)
            ->get(route('customer.export-containers.show', ['exportContainer' => $ownContainer->getKey()]))
            ->assertOk()
            ->assertSee($ownContainer->container_number);

        $this->actingAs($mine)
            ->get(route('customer.export-containers.show', ['exportContainer' => $otherContainer->getKey()]))
            ->assertNotFound();
    }

    public function test_a_customer_can_confirm_an_import_draft_pib(): void
    {
        $user = $this->portalUser();
        $shipment = $this->importShipmentFor($user->companies()->first(), 'BL-IMP-CONFIRM');

        $this->actingAs($user);

        Livewire::test(ImportShipmentDetail::class, ['importShipment' => $shipment->getKey()])
            ->call('confirm')
            ->assertOk();

        $shipment->refresh();

        $this->assertSame(DraftPibConfirmationStatus::Confirmed, $shipment->draft_pib_confirmation_status);
        $this->assertNotNull($shipment->draft_pib_confirmed_at);

        $this->assertDatabaseHas('activity_logs', [
            'import_shipment_id' => $shipment->getKey(),
            'event' => 'draft_pib_confirmed',
            'is_customer_visible' => true,
        ]);
    }

    public function test_an_export_shipment_page_offers_no_draft_pib_actions(): void
    {
        $user = $this->portalUser();
        $shipment = $this->exportShipmentFor($user->companies()->first(), 'BL-EXP-NOPIB');

        $this->actingAs($user);

        Livewire::test(ExportShipmentDetail::class, ['exportShipment' => $shipment->getKey()])
            ->assertOk()
            ->assertDontSee('Draft PIB confirmation')
            ->assertDontSee('Confirm draft PIB');
    }

    public function test_a_confirmed_draft_pib_offers_no_confirm_or_revision_action(): void
    {
        $user = User::query()->where('email', 'sari@java-retail.test')->firstOrFail();
        $shipment = $this->confirmedImportShipment();

        $this->actingAs($user);

        Livewire::test(ImportShipmentDetail::class, ['importShipment' => $shipment->getKey()])
            ->assertOk()
            ->assertSee('Draft PIB confirmation')
            ->assertSee('no action is needed')
            ->assertDontSee('Confirm draft PIB')
            ->assertDontSee('Request revision');
    }

    public function test_a_pending_draft_pib_is_still_pending_and_actionable(): void
    {
        $user = $this->portalUser();
        $shipment = $this->importShipmentFor($user->companies()->first(), 'BL-IMP-PENDING');

        $this->actingAs($user);

        Livewire::test(ImportShipmentDetail::class, ['importShipment' => $shipment->getKey()])
            ->assertOk()
            ->assertSee('Confirm draft PIB')
            ->assertSee('Request revision');
    }

    public function test_a_confirmed_draft_cannot_be_revised_from_the_portal(): void
    {
        $user = User::query()->where('email', 'sari@java-retail.test')->firstOrFail();
        $shipment = $this->confirmedImportShipment();

        $this->actingAs($user);

        Livewire::test(ImportShipmentDetail::class, ['importShipment' => $shipment->getKey()])
            ->set('revisionNotes', 'Please change the HS code.')
            ->call('requestRevision')
            ->assertHasErrors('revisionNotes');

        $this->assertSame(DraftPibConfirmationStatus::Confirmed, $shipment->refresh()->draft_pib_confirmation_status);
        $this->assertDatabaseMissing('activity_logs', [
            'import_shipment_id' => $shipment->getKey(),
            'event' => 'draft_pib_revision_requested',
        ]);
    }

    public function test_confirming_an_already_confirmed_draft_changes_nothing(): void
    {
        $user = User::query()->where('email', 'sari@java-retail.test')->firstOrFail();
        $shipment = $this->confirmedImportShipment();

        $this->actingAs($user);

        $before = ActivityLog::query()->count();
        $confirmedAt = $shipment->draft_pib_confirmed_at;

        Livewire::test(ImportShipmentDetail::class, ['importShipment' => $shipment->getKey()])
            ->call('confirm')
            ->assertOk()
            ->assertSee('already confirmed');

        $this->assertSame($before, ActivityLog::query()->count());
        $this->assertTrue($confirmedAt->equalTo($shipment->refresh()->draft_pib_confirmed_at));
    }

    private function confirmedImportShipment(): ImportShipment
    {
        return ImportShipment::query()
            ->where('draft_pib_confirmation_status', DraftPibConfirmationStatus::Confirmed)
            ->firstOrFail();
    }

    /**
     * Drives the real login flow: requests a code, opens the signed verify page
     * and reads the form action, session id and (in development) the code itself.
     *
     * @return array{action: string, sessionId: string, code: string}
     */
    private function openVerifyForm(User $user): array
    {
        $location = $this->post(route('customer.login.send'), ['email' => $user->email])
            ->assertRedirect()
            ->headers->get('Location');

        parse_str((string) parse_url((string) $location, PHP_URL_QUERY), $query);
        $sessionId = (string) ($query['sessionId'] ?? '');

        // The test client does not carry cookies between requests, so pin the
        // session cookie to the id the code was requested with -- that is what
        // otpz's session lock compares against.
        $html = $this->withCookie(config('session.cookie'), $sessionId)
            ->get($location)
            ->assertOk()
            ->getContent();

        preg_match('/<form[^>]+action="([^"]+)"/', (string) $html, $actionMatch);
        preg_match('/font-semibold">([A-Za-z0-9-]+)/', (string) $html, $codeMatch);

        return [
            'action' => html_entity_decode($actionMatch[1] ?? ''),
            'sessionId' => $sessionId,
            'code' => $codeMatch[1] ?? '',
        ];
    }

    private function portalUser(string $email = 'portal@example.com', string $companyName = 'Portal Company'): User
    {
        $company = Company::query()->firstOrCreate(
            ['name' => $companyName],
            ['code' => strtoupper(substr(md5($companyName), 0, 6)), 'is_active' => true],
        );

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            ['name' => 'Portal User', 'password' => 'password', 'is_active' => true],
        );
        $user->syncRoles([Role::CUSTOMER]);
        $user->companies()->syncWithoutDetaching([$company->getKey()]);

        return $user->refresh();
    }

    private function exportShipmentFor(Company $company, string $reference): ExportShipment
    {
        $shipment = ExportShipment::query()->create([
            'bl_number' => $reference,
            'company_id' => $company->getKey(),
            'company_name_snapshot' => $company->name,
            'current_milestone' => ExportMilestone::DocumentReceived,
        ]);

        $shipment->containers()->create([
            'container_number' => 'PORTAL'.random_int(100000, 999999),
        ]);

        return $shipment->refresh();
    }

    private function importShipmentFor(Company $company, string $reference): ImportShipment
    {
        $shipment = ImportShipment::query()->create([
            'bl_number' => $reference,
            'company_id' => $company->getKey(),
            'company_name_snapshot' => $company->name,
            'current_milestone' => ImportMilestone::DocumentReceived,
            'draft_pib_confirmation_status' => DraftPibConfirmationStatus::Pending,
        ]);

        $shipment->containers()->create([
            'container_number' => 'PORTAL'.random_int(100000, 999999),
        ]);

        return $shipment->refresh();
    }
}
