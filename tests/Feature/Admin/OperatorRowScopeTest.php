<?php

/**
 * File: tests/Feature/Admin/OperatorRowScopeTest.php
 * Responsibility: Verifies the admin panel row-level scope for operators.
 * What it does:
 * - Operators only see shipments, containers and activity logs of the
 *   companies they are assigned to (company_user pivot); out-of-scope edit
 *   URLs 404. Admin and super admin stay unscoped.
 * How to use: `php artisan test --filter=OperatorRowScopeTest`.
 * How to extend: add a test per resource that grows a company_id scope.
 */

namespace Tests\Feature\Admin;

use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Filament\Resources\ExportContainers\Pages\ListExportContainers;
use App\Filament\Resources\ExportShipments\Pages\EditExportShipment;
use App\Filament\Resources\ExportShipments\Pages\ListExportShipments;
use App\Filament\Resources\ImportShipments\Pages\ListImportShipments;
use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\ExportContainer;
use App\Models\ExportShipment;
use App\Models\ImportShipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OperatorRowScopeTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->operator = User::query()->where('email', 'operator@example.com')->firstOrFail();
        $this->admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
    }

    public function test_operator_only_sees_assigned_companies_export_shipments(): void
    {
        $this->actingAs($this->operator);

        $visible = ExportShipment::query()->whereHas('company', fn ($query) => $query->whereIn('code', ['NUS', 'SNI']))->get();
        $hidden = ExportShipment::query()->whereHas('company', fn ($query) => $query->whereNotIn('code', ['NUS', 'SNI']))->get();

        $this->assertNotEmpty($visible);
        $this->assertNotEmpty($hidden);

        Livewire::test(ListExportShipments::class)
            ->assertCanSeeTableRecords($visible)
            ->assertCanNotSeeTableRecords($hidden);
    }

    public function test_operator_only_sees_assigned_companies_import_shipments(): void
    {
        $this->actingAs($this->operator);

        $visible = ImportShipment::query()->whereHas('company', fn ($query) => $query->whereIn('code', ['NUS', 'SNI']))->get();
        $hidden = ImportShipment::query()->whereHas('company', fn ($query) => $query->whereNotIn('code', ['NUS', 'SNI']))->get();

        $this->assertNotEmpty($visible);
        $this->assertNotEmpty($hidden);

        Livewire::test(ListImportShipments::class)
            ->assertCanSeeTableRecords($visible)
            ->assertCanNotSeeTableRecords($hidden);
    }

    public function test_admin_sees_every_shipment(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ListExportShipments::class)
            ->assertCanSeeTableRecords(ExportShipment::query()->get());

        Livewire::test(ListImportShipments::class)
            ->assertCanSeeTableRecords(ImportShipment::query()->get());
    }

    public function test_operator_cannot_open_an_unassigned_shipment(): void
    {
        $hidden = ExportShipment::query()->whereHas('company', fn ($query) => $query->whereNotIn('code', ['NUS', 'SNI']))->firstOrFail();

        $this->actingAs($this->operator)
            ->get(EditExportShipment::getUrl(['record' => $hidden]))
            ->assertNotFound();

        $visible = ExportShipment::query()->whereHas('company', fn ($query) => $query->whereIn('code', ['NUS', 'SNI']))->firstOrFail();

        $this->actingAs($this->operator)
            ->get(EditExportShipment::getUrl(['record' => $visible]))
            ->assertOk();
    }

    public function test_operator_containers_list_is_scoped(): void
    {
        $this->actingAs($this->operator);

        $visible = ExportContainer::query()->whereHas('shipment.company', fn ($query) => $query->whereIn('code', ['NUS', 'SNI']))->get();
        $hidden = ExportContainer::query()->whereHas('shipment.company', fn ($query) => $query->whereNotIn('code', ['NUS', 'SNI']))->get();

        $this->assertNotEmpty($visible);
        $this->assertNotEmpty($hidden);

        Livewire::test(ListExportContainers::class)
            ->assertCanSeeTableRecords($visible)
            ->assertCanNotSeeTableRecords($hidden);
    }

    public function test_operator_activity_logs_are_scoped(): void
    {
        $this->actingAs($this->operator);

        $inScope = ExportShipment::query()->whereHas('company', fn ($query) => $query->whereIn('code', ['NUS', 'SNI']))->firstOrFail();
        $outOfScope = ExportShipment::query()->whereHas('company', fn ($query) => $query->whereNotIn('code', ['NUS', 'SNI']))->firstOrFail();

        $visibleLog = ActivityLog::query()->create([
            'export_shipment_id' => $inScope->getKey(),
            'event' => 'milestone_changed',
            'entity_type' => ExportShipment::class,
            'entity_id' => $inScope->getKey(),
            'occurred_at' => now(),
        ]);
        $hiddenLog = ActivityLog::query()->create([
            'export_shipment_id' => $outOfScope->getKey(),
            'event' => 'milestone_changed',
            'entity_type' => ExportShipment::class,
            'entity_id' => $outOfScope->getKey(),
            'occurred_at' => now(),
        ]);

        Livewire::test(ListActivityLogs::class)
            ->assertCanSeeTableRecords([$visibleLog])
            ->assertCanNotSeeTableRecords([$hiddenLog]);
    }

    public function test_operator_company_picker_only_offers_assigned_companies(): void
    {
        $this->actingAs($this->operator);

        $query = User::scopeToAssignedCompanies(Company::query());

        $this->assertEqualsCanonicalizing(
            ['NUS', 'SNI'],
            $query->pluck('code')->all(),
        );

        $this->actingAs($this->admin);

        $this->assertSame(
            Company::query()->count(),
            User::scopeToAssignedCompanies(Company::query())->count(),
        );
    }
}
