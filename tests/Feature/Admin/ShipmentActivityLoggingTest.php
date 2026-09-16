<?php

/**
 * File: tests/Feature/Admin/ShipmentActivityLoggingTest.php
 * Responsibility: Verifies milestone guidance and shipment change auditing.
 * What it does:
 * - Confirms locked sections name the milestone that unlocks their fields.
 * - Verifies B/L, HS-code and container changes store exact old/new values,
 *   actor attribution, and no technical fields.
 * - Confirms unchanged saves do not produce audit noise.
 * How to use: `php artisan test --filter=ShipmentActivityLoggingTest`.
 * How to extend: Add one case for each new shipment editing surface.
 */

namespace Tests\Feature\Admin;

use App\Enums\ShipmentMilestone;
use App\Filament\Resources\BillOfLadings\Pages\EditBillOfLading;
use App\Filament\Resources\Containers\Pages\CreateContainer;
use App\Filament\Resources\Containers\Pages\EditContainer;
use App\Models\ActivityLog;
use App\Models\Attachment;
use App\Models\BillOfLading;
use App\Models\Container;
use App\Models\HsCode;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShipmentActivityLoggingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->admin = User::factory()->create();
        $this->admin->assignRole(Role::ADMIN);
        $this->actingAs($this->admin);
    }

    public function test_locked_fields_name_the_milestone_that_unlocks_them(): void
    {
        $export = BillOfLading::query()->where('reference_number', 'REF-EXP-0001')->firstOrFail();

        Livewire::test(EditBillOfLading::class, ['record' => $export->getRouteKey()])
            ->assertSee('Locked until Step 2: Checking booking order')
            ->assertDontSee('Current step:')
            ->assertDontSee('Available since');

        $import = BillOfLading::query()->where('reference_number', 'REF-IMP-0001')->firstOrFail();
        $import->update(['current_milestone' => ShipmentMilestone::DraftPib]);

        Livewire::test(EditBillOfLading::class, ['record' => $import->getRouteKey()])
            ->assertSee('Locked until Step 7: DO release');
    }

    public function test_bill_of_lading_save_records_only_changed_fields_and_actor(): void
    {
        $billOfLading = BillOfLading::query()->where('reference_number', 'REF-EXP-0001')->firstOrFail();

        Livewire::test(EditBillOfLading::class, ['record' => $billOfLading->getRouteKey()])
            ->fillForm([
                'bl_number' => 'BL-AUDITED-001',
                'aju_number' => 'AJU-AUDITED-001',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $log = ActivityLog::query()
            ->where('bill_of_lading_id', $billOfLading->getKey())
            ->where('event', 'bill_of_lading_updated')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame($this->admin->getKey(), $log->actor_id);
        $this->assertSame('BL-EXP-0001', $log->old_values['bl_number']);
        $this->assertNull($log->old_values['aju_number']);
        $this->assertSame('BL-AUDITED-001', $log->new_values['bl_number']);
        $this->assertSame('AJU-AUDITED-001', $log->new_values['aju_number']);
        $this->assertCount(2, $log->old_values);
        $this->assertCount(2, $log->new_values);
        $this->assertArrayNotHasKey('updated_at', $log->new_values);
        $this->assertArrayNotHasKey('updated_by', $log->new_values);
        $this->assertFalse($log->is_customer_visible);
    }

    public function test_unchanged_bill_of_lading_save_creates_no_activity(): void
    {
        $billOfLading = BillOfLading::query()->where('reference_number', 'REF-EXP-0001')->firstOrFail();
        $before = ActivityLog::query()->count();

        Livewire::test(EditBillOfLading::class, ['record' => $billOfLading->getRouteKey()])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($before, ActivityLog::query()->count());
    }

    public function test_bill_of_lading_save_records_hs_code_assignments(): void
    {
        $billOfLading = BillOfLading::query()->where('reference_number', 'REF-IMP-0001')->firstOrFail();
        $billOfLading->update(['current_milestone' => ShipmentMilestone::CheckingDocument]);

        $newHsCode = HsCode::query()
            ->whereDoesntHave('billOfLadings', fn ($query) => $query->whereKey($billOfLading->getKey()))
            ->orderBy('code')
            ->firstOrFail();

        $oldCodes = $billOfLading->hsCodes()->orderBy('code')->pluck('code')->all();
        $ids = [...$billOfLading->hsCodes()->pluck('hs_codes.id')->all(), $newHsCode->getKey()];
        $newCodes = [...$oldCodes, $newHsCode->code];
        sort($newCodes);

        Livewire::test(EditBillOfLading::class, ['record' => $billOfLading->getRouteKey()])
            ->fillForm(['hsCodes' => $ids])
            ->call('save')
            ->assertHasNoFormErrors();

        $log = ActivityLog::query()
            ->where('bill_of_lading_id', $billOfLading->getKey())
            ->where('event', 'hs_codes_updated')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame($oldCodes, $log->old_values['hs_codes']);
        $this->assertSame($newCodes, $log->new_values['hs_codes']);
        $this->assertSame($this->admin->getKey(), $log->actor_id);
    }

    public function test_shipment_diff_records_nested_container_create_update_and_remove(): void
    {
        $billOfLading = BillOfLading::query()->where('reference_number', 'REF-EXP-0001')->firstOrFail();
        $containers = $billOfLading->containers()->orderBy('id')->get();
        $this->assertCount(2, $containers);

        $logger = app(ActivityLogger::class);
        $before = $logger->shipmentSnapshot($billOfLading);

        $updated = $containers->first();
        $removed = $containers->last();
        $oldSeal = $updated->seal_number;

        $updated->update(['seal_number' => 'SEAL-AUDITED']);
        $removed->delete();
        $created = $billOfLading->containers()->create(['container_number' => 'AUDIT-CONT-001']);

        $this->assertSame(3, $logger->recordShipmentChanges($billOfLading, $before));

        $updatedLog = ActivityLog::query()
            ->where('event', 'container_updated')
            ->where('entity_id', $updated->getKey())
            ->firstOrFail();
        $this->assertSame(['seal_number' => $oldSeal], $updatedLog->old_values);
        $this->assertSame(['seal_number' => 'SEAL-AUDITED'], $updatedLog->new_values);

        $createdLog = ActivityLog::query()
            ->where('event', 'container_created')
            ->where('entity_id', $created->getKey())
            ->firstOrFail();
        $this->assertNull($createdLog->old_values);
        $this->assertSame('AUDIT-CONT-001', $createdLog->new_values['container_number']);
        $this->assertArrayNotHasKey('bill_of_lading_id', $createdLog->new_values);

        $removedLog = ActivityLog::query()
            ->where('event', 'container_deleted')
            ->where('entity_id', $removed->getKey())
            ->firstOrFail();
        $this->assertSame($removed->container_number, $removedLog->old_values['container_number']);
        $this->assertNull($removedLog->new_values);
    }

    public function test_standalone_container_edit_records_changed_fields(): void
    {
        $container = Container::query()->firstOrFail();

        Livewire::test(EditContainer::class, ['record' => $container->getRouteKey()])
            ->fillForm(['driver_name' => 'Audit Driver'])
            ->call('save')
            ->assertHasNoFormErrors();

        $log = ActivityLog::query()
            ->where('event', 'container_updated')
            ->where('entity_id', $container->getKey())
            ->latest('id')
            ->firstOrFail();

        $this->assertNull($log->old_values['driver_name']);
        $this->assertSame('Audit Driver', $log->new_values['driver_name']);
        $this->assertSame($this->admin->getKey(), $log->actor_id);
        $this->assertCount(1, $log->new_values);
    }

    public function test_standalone_container_create_records_initial_values(): void
    {
        $billOfLading = BillOfLading::query()->where('reference_number', 'REF-EXP-0001')->firstOrFail();

        Livewire::test(CreateContainer::class)
            ->fillForm([
                'bill_of_lading_id' => $billOfLading->getKey(),
                'container_number' => 'AUDIT-CONT-NEW',
                'size' => '40',
                'type' => 'HC',
                'stuffing_status' => 'not_started',
                'status' => 'pending',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $container = Container::query()->where('container_number', 'AUDIT-CONT-NEW')->firstOrFail();
        $log = ActivityLog::query()
            ->where('event', 'container_created')
            ->where('entity_id', $container->getKey())
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('AUDIT-CONT-NEW', $log->new_values['container_number']);
        $this->assertSame('40', $log->new_values['size']);
        $this->assertSame('HC', $log->new_values['type']);
        $this->assertSame($this->admin->getKey(), $log->actor_id);
        $this->assertArrayNotHasKey('id', $log->new_values);
        $this->assertArrayNotHasKey('bill_of_lading_id', $log->new_values);
        $this->assertFalse($log->is_customer_visible);
    }

    public function test_container_attachment_picker_syncs_media_rows(): void
    {
        $container = Container::query()->firstOrFail();
        $media = fn (array $extra = []): Attachment => Attachment::query()->create([
            'disk' => 'public',
            'name' => 'doc.pdf',
            'path' => 'uploads/doc.pdf',
            'type' => 'application/pdf',
            'ext' => 'pdf',
        ] + $extra);

        $linked = $media(['container_id' => $container->getKey()]);
        $picked = $media();
        $unpicked = $media(['container_id' => $container->getKey()]);

        // CuratorPicker state is a uuid-keyed array of full media arrays, so
        // set() (not fillForm()) with complete media payloads mimics picking.
        Livewire::test(EditContainer::class, ['record' => $container->getRouteKey()])
            ->set('data.attachment_items', [
                $linked->fresh()->toArray(),
                $picked->fresh()->toArray(),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($container->getKey(), $picked->fresh()->container_id);
        $this->assertSame($container->bill_of_lading_id, $picked->fresh()->bill_of_lading_id);
        $this->assertNull($unpicked->fresh()->container_id);

        $log = ActivityLog::query()
            ->where('event', 'container_updated')
            ->where('entity_id', $container->getKey())
            ->latest('id')
            ->firstOrFail();

        $this->assertSame(
            [$linked->getKey(), $unpicked->getKey()],
            $log->old_values['attachments'],
        );
        $this->assertSame(
            [$linked->getKey(), $picked->getKey()],
            $log->new_values['attachments'],
        );
    }
}
