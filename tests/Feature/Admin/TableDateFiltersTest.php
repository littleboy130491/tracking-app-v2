<?php

/**
 * File: tests/Feature/Admin/TableDateFiltersTest.php
 * Responsibility: Guards the Year / Month / date-range filters on the admin lists.
 * What it does:
 * - Proves the shared DateFilters helper narrows the export B/L list by year,
 *   by month, and by a From/Until range (including clearing a filter again).
 * - Proves the same filters are wired into the import B/L list, both container
 *   lists and both company relation managers.
 * How to use: `php artisan test --filter=TableDateFiltersTest`.
 * How to extend: add a case when another list gains DateFilters::make().
 */

namespace Tests\Feature\Admin;

use App\Enums\ExportMilestone;
use App\Enums\ImportMilestone;
use App\Enums\ShipmentStatus;
use App\Filament\Resources\Companies\Pages\EditCompany;
use App\Filament\Resources\Companies\RelationManagers\ExportShipmentsRelationManager;
use App\Filament\Resources\Companies\RelationManagers\ImportShipmentsRelationManager;
use App\Filament\Resources\ExportContainers\Pages\ListExportContainers;
use App\Filament\Resources\ExportShipments\Pages\ListExportShipments;
use App\Filament\Resources\ImportContainers\Pages\ListImportContainers;
use App\Filament\Resources\ImportShipments\Pages\ListImportShipments;
use App\Models\Company;
use App\Models\ExportShipment;
use App\Models\ImportShipment;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TableDateFiltersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_export_shipment_list_filters_by_year_month_and_range(): void
    {
        $this->actingAs($this->admin());

        $company = $this->company();
        $may = $this->backdate($this->exportShipment($company, 1), '2024-05-10 08:00:00');
        $august = $this->backdate($this->exportShipment($company, 2), '2024-08-20 08:00:00');
        $otherYear = $this->backdate($this->exportShipment($company, 3), '2023-11-05 08:00:00');

        Livewire::test(ListExportShipments::class)
            ->filterTable('created_year', 2024)
            ->assertCanSeeTableRecords([$may, $august])
            ->assertCanNotSeeTableRecords([$otherYear])
            ->filterTable('created_month', 5)
            ->assertCanSeeTableRecords([$may])
            ->assertCanNotSeeTableRecords([$august, $otherYear])
            ->filterTable('created_month', null)
            ->filterTable('created_year', null)
            ->assertCanSeeTableRecords([$may, $august, $otherYear])
            ->filterTable('created_between', ['from' => '2024-01-01', 'until' => '2024-06-30'])
            ->assertCanSeeTableRecords([$may])
            ->assertCanNotSeeTableRecords([$august, $otherYear]);
    }

    public function test_import_shipment_list_filters_by_year(): void
    {
        $this->actingAs($this->admin());

        $company = $this->company();
        $inYear = $this->backdate($this->importShipment($company, 1), '2024-05-10 08:00:00');
        $otherYear = $this->backdate($this->importShipment($company, 2), '2023-11-05 08:00:00');

        Livewire::test(ListImportShipments::class)
            ->filterTable('created_year', 2024)
            ->assertCanSeeTableRecords([$inYear])
            ->assertCanNotSeeTableRecords([$otherYear]);
    }

    public function test_export_container_list_filters_by_year(): void
    {
        $this->actingAs($this->admin());

        $shipment = $this->exportShipment($this->company(), 1);
        $inYear = $this->backdate($shipment->containers()->create(['container_number' => 'DATE-EXP-0001']), '2024-05-10 08:00:00');
        $otherYear = $this->backdate($shipment->containers()->create(['container_number' => 'DATE-EXP-0002']), '2023-11-05 08:00:00');

        Livewire::test(ListExportContainers::class)
            ->filterTable('created_year', 2024)
            ->assertCanSeeTableRecords([$inYear])
            ->assertCanNotSeeTableRecords([$otherYear]);
    }

    public function test_import_container_list_filters_by_year(): void
    {
        $this->actingAs($this->admin());

        $shipment = $this->importShipment($this->company(), 1);
        $inYear = $this->backdate($shipment->containers()->create(['container_number' => 'DATE-IMP-0001']), '2024-05-10 08:00:00');
        $otherYear = $this->backdate($shipment->containers()->create(['container_number' => 'DATE-IMP-0002']), '2023-11-05 08:00:00');

        Livewire::test(ListImportContainers::class)
            ->filterTable('created_year', 2024)
            ->assertCanSeeTableRecords([$inYear])
            ->assertCanNotSeeTableRecords([$otherYear]);
    }

    public function test_company_export_shipments_relation_manager_filters_by_year(): void
    {
        $this->actingAs($this->admin());

        $company = $this->company();
        $inYear = $this->backdate($this->exportShipment($company, 1), '2024-05-10 08:00:00');
        $otherYear = $this->backdate($this->exportShipment($company, 2), '2023-11-05 08:00:00');

        Livewire::test(ExportShipmentsRelationManager::class, [
            'ownerRecord' => $company,
            'pageClass' => EditCompany::class,
        ])
            ->filterTable('created_year', 2024)
            ->assertCanSeeTableRecords([$inYear])
            ->assertCanNotSeeTableRecords([$otherYear]);
    }

    public function test_company_import_shipments_relation_manager_filters_by_year(): void
    {
        $this->actingAs($this->admin());

        $company = $this->company();
        $inYear = $this->backdate($this->importShipment($company, 1), '2024-05-10 08:00:00');
        $otherYear = $this->backdate($this->importShipment($company, 2), '2023-11-05 08:00:00');

        Livewire::test(ImportShipmentsRelationManager::class, [
            'ownerRecord' => $company,
            'pageClass' => EditCompany::class,
        ])
            ->filterTable('created_year', 2024)
            ->assertCanSeeTableRecords([$inYear])
            ->assertCanNotSeeTableRecords([$otherYear]);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        return $admin;
    }

    private function company(): Company
    {
        return Company::query()->orderBy('id')->firstOrFail();
    }

    private function exportShipment(Company $company, int $number): ExportShipment
    {
        return ExportShipment::query()->create([
            'bl_number' => "BL-DATE-EXP-{$number}",
            'company_id' => $company->getKey(),
            'company_name_snapshot' => $company->name,
            'current_milestone' => ExportMilestone::DocumentReceived,
            'status' => ShipmentStatus::InProgress,
        ]);
    }

    private function importShipment(Company $company, int $number): ImportShipment
    {
        return ImportShipment::query()->create([
            'bl_number' => "BL-DATE-IMP-{$number}",
            'company_id' => $company->getKey(),
            'company_name_snapshot' => $company->name,
            'current_milestone' => ImportMilestone::DocumentReceived,
            'status' => ShipmentStatus::InProgress,
            'confirmation_checklist' => false,
        ]);
    }

    /**
     * Move a record's created_at so the date filters can separate it.
     *
     * @template TModel of Model
     *
     * @param  TModel  $record
     * @return TModel
     */
    private function backdate(Model $record, string $createdAt): Model
    {
        $record->forceFill(['created_at' => $createdAt])->save();

        return $record;
    }
}
