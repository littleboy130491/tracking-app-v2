<?php

/**
 * File: tests/Feature/Admin/ShipmentActivityLoggingTest.php
 * Responsibility: Verifies milestone guidance and shipment change auditing.
 * What it does:
 * - Confirms locked sections name the milestone that unlocks their fields.
 * - Verifies shipment, HS-code and container changes store exact old/new
 *   values, actor attribution, and no technical fields.
 * - Confirms unchanged saves do not produce audit noise.
 * How to use: `php artisan test --filter=ShipmentActivityLoggingTest`.
 * How to extend: Add one case for each new shipment editing surface.
 */

namespace Tests\Feature\Admin;

use App\Enums\ExportMilestone;
use App\Enums\ImportMilestone;
use App\Filament\Resources\ExportContainers\Pages\CreateExportContainer;
use App\Filament\Resources\ExportContainers\Pages\EditExportContainer;
use App\Filament\Resources\ExportShipments\Pages\EditExportShipment;
use App\Filament\Resources\ImportShipments\Pages\EditImportShipment;
use App\Models\ActivityLog;
use App\Models\Attachment;
use App\Models\ExportContainer;
use App\Models\ExportShipment;
use App\Models\HsCode;
use App\Models\ImportShipment;
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
        $export = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();

        Livewire::test(EditExportShipment::class, ['record' => $export->getRouteKey()])
            ->assertSee('Locked until Step 2: Checking booking order')
            ->assertSee('data-bl-ms-goto="2"', false)
            ->assertDontSee('Current step:')
            ->assertDontSee('Available since');

        $import = ImportShipment::query()->where('bl_number', 'BL-IMP-0001')->firstOrFail();
        $import->update(['current_milestone' => ImportMilestone::DraftPib]);

        Livewire::test(EditImportShipment::class, ['record' => $import->getRouteKey()])
            ->assertSee('Locked until Step 7: DO release')
            ->assertSee('data-bl-ms-goto="7"', false);
    }

    public function test_shipment_save_records_only_changed_fields_and_actor(): void
    {
        $export = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();
        // AJU/B/L numbers are gated at "Checking booking order"; advance so the
        // form actually dehydrates them and they can be audited.
        $export->update(['current_milestone' => ExportMilestone::CheckingBookingOrder]);

        Livewire::test(EditExportShipment::class, ['record' => $export->getRouteKey()])
            ->fillForm([
                'bl_number' => 'BL-AUDITED-001',
                'aju_number' => 'AJU-AUDITED-001',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $log = ActivityLog::query()
            ->where('export_shipment_id', $export->getKey())
            ->where('event', 'shipment_updated')
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

        // Every recorded event stamps the denormalized latest-event columns.
        $this->assertSame('shipment_updated', $export->refresh()->latest_event);
        $this->assertNotNull($export->latest_event_at);
    }

    public function test_unchanged_shipment_save_creates_no_activity(): void
    {
        $export = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();
        $before = ActivityLog::query()->count();

        Livewire::test(EditExportShipment::class, ['record' => $export->getRouteKey()])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($before, ActivityLog::query()->count());
    }

    public function test_shipment_save_records_hs_code_assignments(): void
    {
        $import = ImportShipment::query()->where('bl_number', 'BL-IMP-0001')->firstOrFail();
        $import->update(['current_milestone' => ImportMilestone::CheckingDocument]);

        $newHsCode = HsCode::query()
            ->whereDoesntHave('importShipments', fn ($query) => $query->whereKey($import->getKey()))
            ->orderBy('code')
            ->firstOrFail();

        $oldCodes = $import->hsCodes()->orderBy('code')->pluck('code')->all();
        $ids = [...$import->hsCodes()->pluck('hs_codes.id')->all(), $newHsCode->getKey()];
        $newCodes = [...$oldCodes, $newHsCode->code];
        sort($newCodes);

        Livewire::test(EditImportShipment::class, ['record' => $import->getRouteKey()])
            ->fillForm(['hsCodes' => $ids])
            ->call('save')
            ->assertHasNoFormErrors();

        $log = ActivityLog::query()
            ->where('import_shipment_id', $import->getKey())
            ->where('event', 'hs_codes_updated')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame($oldCodes, $log->old_values['hs_codes']);
        $this->assertSame($newCodes, $log->new_values['hs_codes']);
        $this->assertSame($this->admin->getKey(), $log->actor_id);
    }

    public function test_shipment_diff_records_nested_container_create_update_and_remove(): void
    {
        $export = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();
        $containers = $export->containers()->orderBy('id')->get();
        $this->assertCount(2, $containers);

        $logger = app(ActivityLogger::class);
        $before = $logger->shipmentSnapshot($export);

        $updated = $containers->first();
        $removed = $containers->last();
        $oldSeal = $updated->seal_number;

        $updated->update(['seal_number' => 'SEAL-AUDITED']);
        $removed->delete();
        $created = $export->containers()->create(['container_number' => 'AUDIT-CONT-001']);

        $this->assertSame(3, $logger->recordShipmentChanges($export, $before));

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
        $this->assertArrayNotHasKey('export_shipment_id', $createdLog->new_values);

        $removedLog = ActivityLog::query()
            ->where('event', 'container_deleted')
            ->where('entity_id', $removed->getKey())
            ->firstOrFail();
        $this->assertSame($removed->container_number, $removedLog->old_values['container_number']);
        $this->assertNull($removedLog->new_values);
    }

    public function test_standalone_container_edit_records_changed_fields(): void
    {
        $container = ExportContainer::query()->firstOrFail();

        Livewire::test(EditExportContainer::class, ['record' => $container->getRouteKey()])
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
        $export = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();

        Livewire::test(CreateExportContainer::class)
            ->fillForm([
                'export_shipment_id' => $export->getKey(),
                'container_number' => 'AUDIT-CONT-NEW',
                'size' => '40',
                'type' => 'HC',
                'stuffing_status' => 'on_process',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $container = ExportContainer::query()->where('container_number', 'AUDIT-CONT-NEW')->firstOrFail();
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
        $this->assertArrayNotHasKey('export_shipment_id', $log->new_values);
        $this->assertFalse($log->is_customer_visible);
    }

    public function test_container_attachment_picker_syncs_media_rows(): void
    {
        $container = ExportContainer::query()->firstOrFail();
        $media = fn (array $extra = []): Attachment => Attachment::query()->create([
            'disk' => 'public',
            'name' => 'doc.pdf',
            'path' => 'uploads/doc.pdf',
            'type' => 'application/pdf',
            'ext' => 'pdf',
        ] + $extra);

        $linked = $media(['export_container_id' => $container->getKey(), 'category' => 'door_photo']);
        $picked = $media();
        $unpicked = $media(['export_container_id' => $container->getKey(), 'category' => 'door_photo']);

        // CuratorPicker state is a uuid-keyed array of full media arrays, so
        // set() (not fillForm()) with complete media payloads mimics picking.
        Livewire::test(EditExportContainer::class, ['record' => $container->getRouteKey()])
            ->set('data.photo_door_items', [
                $linked->fresh()->toArray(),
                $picked->fresh()->toArray(),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($container->getKey(), $picked->fresh()->export_container_id);
        $this->assertSame($container->export_shipment_id, $picked->fresh()->export_shipment_id);
        $this->assertSame('door_photo', $picked->fresh()->category?->value);
        $this->assertNull($unpicked->fresh()->export_container_id);

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
