<?php

/**
 * File: tests/Feature/Admin/ContainerNumberUniquenessTest.php
 * Responsibility: Guards container-number uniqueness (per shipment, active rows only).
 * What it does:
 * - Proves a soft-deleted container number can be re-added to its shipment
 *   (the production crash this guards against).
 * - Proves an active duplicate on the same shipment is still rejected by the
 *   database and reported as a friendly form error, not a 500.
 * How to use: `php artisan test --filter=ContainerNumberUniquenessTest`.
 * How to extend: add a pair of re-add/reject tests per new container table.
 */

namespace Tests\Feature\Admin;

use App\Filament\Resources\ExportContainers\Pages\EditExportContainer;
use App\Filament\Resources\ExportShipments\Pages\EditExportShipment;
use App\Models\ExportShipment;
use App\Models\ImportShipment;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContainerNumberUniquenessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        return $admin;
    }

    public function test_a_soft_deleted_export_container_number_can_be_readded(): void
    {
        $shipment = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();
        $container = $shipment->containers()->firstOrFail();

        $container->delete();
        $this->assertSoftDeleted($container);

        // Re-adding the same number on the same shipment used to crash with
        // "UNIQUE constraint failed" because the trashed row held the slot.
        $shipment->containers()->create(['container_number' => $container->container_number]);

        $this->assertDatabaseHas('export_containers', [
            'export_shipment_id' => $shipment->id,
            'container_number' => $container->container_number,
            'deleted_at' => null,
        ]);
    }

    public function test_a_soft_deleted_import_container_number_can_be_readded(): void
    {
        $shipment = ImportShipment::query()->where('bl_number', 'BL-IMP-0001')->firstOrFail();
        $container = $shipment->containers()->firstOrFail();

        $container->delete();
        $this->assertSoftDeleted($container);

        $shipment->containers()->create(['container_number' => $container->container_number]);

        $this->assertDatabaseHas('import_containers', [
            'import_shipment_id' => $shipment->id,
            'container_number' => $container->container_number,
            'deleted_at' => null,
        ]);
    }

    public function test_an_active_duplicate_export_container_number_is_still_rejected(): void
    {
        $shipment = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();
        $container = $shipment->containers()->firstOrFail();

        $this->expectException(UniqueConstraintViolationException::class);

        $shipment->containers()->create(['container_number' => $container->container_number]);
    }

    public function test_an_active_duplicate_import_container_number_is_still_rejected(): void
    {
        $shipment = ImportShipment::query()->where('bl_number', 'BL-IMP-0001')->firstOrFail();
        $container = $shipment->containers()->firstOrFail();

        $this->expectException(UniqueConstraintViolationException::class);

        $shipment->containers()->create(['container_number' => $container->container_number]);
    }

    public function test_the_same_container_number_stays_allowed_on_another_bill_of_lading(): void
    {
        $source = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();
        $other = ExportShipment::query()->where('bl_number', 'BL-EXP-0002')->firstOrFail();
        $number = $source->containers()->firstOrFail()->container_number;

        $other->containers()->create(['container_number' => $number]);

        $this->assertDatabaseHas('export_containers', [
            'export_shipment_id' => $other->id,
            'container_number' => $number,
            'deleted_at' => null,
        ]);
    }

    public function test_the_standalone_container_form_reports_a_duplicate_as_a_form_error(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $shipment = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();
        $container = $shipment->containers()->firstOrFail();
        $sibling = $shipment->containers()->whereKeyNot($container)->firstOrFail();
        $originalNumber = $container->container_number;

        Livewire::test(EditExportContainer::class, ['record' => $container->getRouteKey()])
            ->set('data.container_number', $sibling->container_number)
            ->call('save')
            ->assertHasFormErrors(['container_number']);

        // The blocked save must not have renamed the container.
        $this->assertDatabaseHas('export_containers', [
            'id' => $container->id,
            'container_number' => $originalNumber,
        ]);
    }

    public function test_the_standalone_container_form_accepts_a_number_used_on_another_bill_of_lading(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $source = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();
        $other = ExportShipment::query()->where('bl_number', 'BL-EXP-0002')->firstOrFail();
        $container = $other->containers()->firstOrFail();
        $number = $source->containers()->firstOrFail()->container_number;

        Livewire::test(EditExportContainer::class, ['record' => $container->getRouteKey()])
            ->set('data.container_number', $number)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('export_containers', [
            'id' => $container->id,
            'container_number' => $number,
        ]);
    }

    public function test_saving_the_shipment_form_keeps_its_own_container_numbers_valid(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $shipment = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();

        // Saving unchanged data must not flag a container's own number as a
        // duplicate — the repeater items ignore their own record.
        Livewire::test(EditExportShipment::class, ['record' => $shipment->getRouteKey()])
            ->call('save')
            ->assertHasNoFormErrors();
    }
}
