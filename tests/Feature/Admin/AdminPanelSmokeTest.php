<?php

/**
 * File: tests/Feature/Admin/AdminPanelSmokeTest.php
 * Responsibility: Guards the admin panel against runtime errors.
 * What it does:
 * - Seeds the database and, as an admin, opens every resource list page.
 * - Asserts the customer-portal role cannot reach the panel.
 * How to use: `php artisan test --filter=AdminPanelSmokeTest`.
 * How to extend: Add create/edit page assertions as resources grow.
 */

namespace Tests\Feature\Admin;

use App\Enums\BillOfLadingStatus;
use App\Enums\ShipmentMilestone;
use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Filament\Resources\BillOfLadings\BillOfLadingResource;
use App\Filament\Resources\BillOfLadings\Pages\CreateBillOfLading;
use App\Filament\Resources\BillOfLadings\Pages\EditBillOfLading;
use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\Companies\Pages\EditCompany;
use App\Filament\Resources\Companies\RelationManagers\BillOfLadingsRelationManager;
use App\Filament\Resources\Containers\ContainerResource;
use App\Filament\Resources\HsCodes\HsCodeResource;
use App\Filament\Resources\HsCodes\Pages\CreateHsCode;
use App\Filament\Resources\Users\UserResource;
use App\Models\BillOfLading;
use App\Models\Company;
use App\Models\HsCode;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_admin_can_open_every_resource_list_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $urls = [
            UserResource::getUrl('index'),
            CompanyResource::getUrl('index'),
            BillOfLadingResource::getUrl('index'),
            ContainerResource::getUrl('index'),
            HsCodeResource::getUrl('index'),
            ActivityLogResource::getUrl('index'),
        ];

        foreach ($urls as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_admin_can_open_the_bill_of_lading_edit_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $billOfLading = BillOfLading::query()->firstOrFail();

        $this->actingAs($admin)
            ->get(BillOfLadingResource::getUrl('edit', ['record' => $billOfLading]))
            ->assertOk()
            ->assertSee('Progress')
            ->assertSee('Advance')
            ->assertSee('jumpToMilestone');
    }

    public function test_admin_can_create_a_bill_of_lading_with_just_the_document_fields(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $company = Company::query()->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(CreateBillOfLading::class)
            ->assertDontSee('Progress')
            ->fillForm([
                'aju_number' => 'AJU-TEST-001',
                'bl_number' => 'BL-TEST-001',
                'shipment_type' => 'import',
                'company_id' => $company->getKey(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $billOfLading = BillOfLading::query()->where('aju_number', 'AJU-TEST-001')->firstOrFail();

        $this->assertNotNull($billOfLading->reference_number);
        $this->assertSame($company->name, $billOfLading->company_name_snapshot);
    }

    public function test_admin_can_create_an_hs_code(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $this->actingAs($admin);

        Livewire::test(CreateHsCode::class)
            ->fillForm([
                'code' => '9999.99',
                'description' => 'Test code',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('hs_codes', ['code' => '9999.99']);
    }

    public function test_admin_can_attach_an_hs_code_on_the_bill_of_lading_form(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $hsCode = HsCode::query()->firstOrFail();

        // Cargo & terminal is milestone-gated, so the shipment must have
        // reached "checking document" before HS codes can be attached.
        $billOfLading = BillOfLading::query()->where('reference_number', 'REF-IMP-0001')->firstOrFail();
        $billOfLading->update(['current_milestone' => ShipmentMilestone::CheckingDocument]);

        $this->actingAs($admin);

        Livewire::test(EditBillOfLading::class, ['record' => $billOfLading->getRouteKey()])
            ->fillForm(['hsCodes' => [$hsCode->getKey()]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($billOfLading->refresh()->hsCodes->contains($hsCode));
    }

    public function test_shipment_milestones_advance_regress_and_complete(): void
    {
        $export = BillOfLading::query()->where('reference_number', 'REF-EXP-0001')->firstOrFail();

        $this->assertSame(ShipmentMilestone::DocumentReceived, $export->current_milestone);

        $export->advanceMilestone();
        $this->assertSame(ShipmentMilestone::CheckingBookingOrder, $export->current_milestone);

        $export->regressMilestone();
        $this->assertSame(ShipmentMilestone::DocumentReceived, $export->current_milestone);
        $this->assertNull($export->previousMilestone());

        foreach (range(1, 10) as $ignored) {
            $export->advanceMilestone();
        }

        $this->assertSame(ShipmentMilestone::FinalChecking, $export->current_milestone);
        $this->assertSame(BillOfLadingStatus::Completed, $export->status);
        $this->assertNotNull($export->completed_at);

        $export->regressMilestone();
        $this->assertSame(BillOfLadingStatus::InProgress, $export->status);
        $this->assertNull($export->completed_at);
    }

    public function test_milestone_stepper_jumps_and_logs_each_change(): void
    {
        $export = BillOfLading::query()->where('reference_number', 'REF-EXP-0001')->firstOrFail();

        // Jumping forward skips the steps in between.
        $export->moveToMilestone(ShipmentMilestone::GateInCy);
        $this->assertSame(ShipmentMilestone::GateInCy, $export->current_milestone);

        // A milestone outside this type's sequence is ignored.
        $export->moveToMilestone(ShipmentMilestone::EmptyReturned);
        $this->assertSame(ShipmentMilestone::GateInCy, $export->current_milestone);

        // Jumping to the last milestone completes; jumping off it reopens.
        $export->moveToMilestone(ShipmentMilestone::FinalChecking);
        $this->assertSame(BillOfLadingStatus::Completed, $export->status);

        $export->moveToMilestone(ShipmentMilestone::DocumentReceived);
        $this->assertSame(BillOfLadingStatus::InProgress, $export->status);
        $this->assertNull($export->completed_at);

        // Each real change lands in the activity log (the ignored jump did not).
        $this->assertSame(3, $export->activityLogs()->where('event', 'milestone_changed')->count());
    }

    public function test_stepper_moves_at_most_one_step_forward(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $billOfLading = BillOfLading::query()->where('reference_number', 'REF-EXP-0001')->firstOrFail();
        $this->assertSame(ShipmentMilestone::DocumentReceived, $billOfLading->current_milestone);

        $this->actingAs($admin);

        // Two steps ahead is rejected — only the next step may be jumped to.
        Livewire::test(EditBillOfLading::class, ['record' => $billOfLading->getRouteKey()])
            ->mountAction('jumpToMilestone', ['milestone' => 'pickup_empty_container'])
            ->callMountedAction();

        $this->assertSame(ShipmentMilestone::DocumentReceived, $billOfLading->refresh()->current_milestone);

        // One step forward works, and jumping back is unrestricted.
        Livewire::test(EditBillOfLading::class, ['record' => $billOfLading->getRouteKey()])
            ->mountAction('jumpToMilestone', ['milestone' => 'checking_booking_order'])
            ->callMountedAction()
            ->mountAction('jumpToMilestone', ['milestone' => 'document_received'])
            ->callMountedAction();

        $this->assertSame(ShipmentMilestone::DocumentReceived, $billOfLading->refresh()->current_milestone);
    }

    public function test_import_milestones_skip_the_spjm_branch_unless_the_response_is_spjm(): void
    {
        $spjm = BillOfLading::query()->where('reference_number', 'REF-IMP-0001')->firstOrFail();
        $spjm->update(['current_milestone' => ShipmentMilestone::BillingResponseReceived]);
        $spjm->advanceMilestone();
        $this->assertSame(ShipmentMilestone::DocumentsUploaded, $spjm->current_milestone);

        $sppb = BillOfLading::query()->where('reference_number', 'REF-IMP-0002')->firstOrFail();
        $sppb->update(['current_milestone' => ShipmentMilestone::BillingResponseReceived]);
        $sppb->advanceMilestone();
        $this->assertSame(ShipmentMilestone::GateOutCy, $sppb->current_milestone);
    }

    public function test_company_list_links_portal_users_to_their_edit_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $company = Company::query()->whereHas('users')->firstOrFail();
        $user = $company->users()->firstOrFail();

        $this->actingAs($admin)
            ->get(CompanyResource::getUrl('index'))
            ->assertOk()
            ->assertSee(UserResource::getUrl('edit', ['record' => $user]), false);
    }

    public function test_company_edit_page_lists_only_the_companys_bill_of_ladings(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $company = Company::query()->whereHas('billOfLadings')->firstOrFail();
        $own = $company->billOfLadings()->first();
        $other = BillOfLading::query()->whereKeyNot($company->billOfLadings()->pluck('id'))->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(BillOfLadingsRelationManager::class, [
            'ownerRecord' => $company,
            'pageClass' => EditCompany::class,
        ])
            ->assertCanSeeTableRecords([$own])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_customer_role_cannot_access_the_admin_panel(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole(Role::CUSTOMER);

        $this->actingAs($customer)
            ->get(UserResource::getUrl('index'))
            ->assertForbidden();
    }
}
