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
use App\Filament\Resources\BillOfLadings\Pages\EditBillOfLading;
use App\Filament\Resources\BillOfLadings\Pages\ListBillOfLadings;
use App\Filament\Resources\Containers\Pages\ListContainers;
use App\Models\ActivityLog;
use App\Models\BillOfLading;
use App\Models\Company;
use App\Models\Container;
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

    public function test_operator_only_sees_assigned_companies_shipments(): void
    {
        $this->actingAs($this->operator);

        $visible = BillOfLading::query()->whereHas('company', fn ($query) => $query->whereIn('code', ['NUS', 'SNI']))->get();
        $hidden = BillOfLading::query()->whereHas('company', fn ($query) => $query->whereNotIn('code', ['NUS', 'SNI']))->get();

        $this->assertNotEmpty($visible);
        $this->assertNotEmpty($hidden);

        Livewire::test(ListBillOfLadings::class)
            ->assertCanSeeTableRecords($visible)
            ->assertCanNotSeeTableRecords($hidden);
    }

    public function test_admin_sees_every_shipment(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ListBillOfLadings::class)
            ->assertCanSeeTableRecords(BillOfLading::query()->get());
    }

    public function test_operator_cannot_open_an_unassigned_shipment(): void
    {
        $hidden = BillOfLading::query()->whereHas('company', fn ($query) => $query->whereNotIn('code', ['NUS', 'SNI']))->firstOrFail();

        $this->actingAs($this->operator)
            ->get(EditBillOfLading::getUrl(['record' => $hidden]))
            ->assertNotFound();

        $visible = BillOfLading::query()->whereHas('company', fn ($query) => $query->whereIn('code', ['NUS', 'SNI']))->firstOrFail();

        $this->actingAs($this->operator)
            ->get(EditBillOfLading::getUrl(['record' => $visible]))
            ->assertOk();
    }

    public function test_operator_containers_list_is_scoped(): void
    {
        $this->actingAs($this->operator);

        $visible = Container::query()->whereHas('billOfLading.company', fn ($query) => $query->whereIn('code', ['NUS', 'SNI']))->get();
        $hidden = Container::query()->whereHas('billOfLading.company', fn ($query) => $query->whereNotIn('code', ['NUS', 'SNI']))->get();

        $this->assertNotEmpty($visible);
        $this->assertNotEmpty($hidden);

        Livewire::test(ListContainers::class)
            ->assertCanSeeTableRecords($visible)
            ->assertCanNotSeeTableRecords($hidden);
    }

    public function test_operator_activity_logs_are_scoped(): void
    {
        $this->actingAs($this->operator);

        $inScope = BillOfLading::query()->whereHas('company', fn ($query) => $query->whereIn('code', ['NUS', 'SNI']))->firstOrFail();
        $outOfScope = BillOfLading::query()->whereHas('company', fn ($query) => $query->whereNotIn('code', ['NUS', 'SNI']))->firstOrFail();

        $visibleLog = ActivityLog::query()->create([
            'bill_of_lading_id' => $inScope->getKey(),
            'event' => 'milestone_changed',
            'entity_type' => BillOfLading::class,
            'entity_id' => $inScope->getKey(),
            'occurred_at' => now(),
        ]);
        $hiddenLog = ActivityLog::query()->create([
            'bill_of_lading_id' => $outOfScope->getKey(),
            'event' => 'milestone_changed',
            'entity_type' => BillOfLading::class,
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
