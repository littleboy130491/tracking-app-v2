<?php

/**
 * File: tests/Feature/Admin/AdminPanelSmokeTest.php
 * Responsibility: Guards the admin panel against runtime errors.
 * What it does:
 * - Seeds the database and, as an admin, opens every resource list and edit page.
 * - Covers shipment creation, HS codes, milestone transitions, the stepper and
 *   the company relation managers.
 * How to use: `php artisan test --filter=AdminPanelSmokeTest`.
 * How to extend: Add create/edit page assertions as resources grow.
 */

namespace Tests\Feature\Admin;

use App\Enums\ExportMilestone;
use App\Enums\ImportMilestone;
use App\Enums\ShipmentStatus;
use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\Companies\Pages\CreateCompany;
use App\Filament\Resources\Companies\Pages\EditCompany;
use App\Filament\Resources\Companies\RelationManagers\ExportShipmentsRelationManager;
use App\Filament\Resources\Companies\RelationManagers\ImportShipmentsRelationManager;
use App\Filament\Resources\ExportContainers\ExportContainerResource;
use App\Filament\Resources\ExportShipments\ExportShipmentResource;
use App\Filament\Resources\ExportShipments\Pages\CreateExportShipment;
use App\Filament\Resources\ExportShipments\Pages\EditExportShipment;
use App\Filament\Resources\HsCodes\HsCodeResource;
use App\Filament\Resources\HsCodes\Pages\CreateHsCode;
use App\Filament\Resources\ImportContainers\ImportContainerResource;
use App\Filament\Resources\ImportShipments\ImportShipmentResource;
use App\Filament\Resources\ImportShipments\Pages\CreateImportShipment;
use App\Filament\Resources\ImportShipments\Pages\EditImportShipment;
use App\Filament\Resources\Users\UserResource;
use App\Models\Company;
use App\Models\ExportShipment;
use App\Models\HsCode;
use App\Models\ImportShipment;
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

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        return $admin;
    }

    public function test_admin_can_open_every_resource_list_page(): void
    {
        $admin = $this->admin();

        $urls = [
            UserResource::getUrl('index'),
            CompanyResource::getUrl('index'),
            ExportShipmentResource::getUrl('index'),
            ImportShipmentResource::getUrl('index'),
            ExportContainerResource::getUrl('index'),
            ImportContainerResource::getUrl('index'),
            HsCodeResource::getUrl('index'),
            ActivityLogResource::getUrl('index'),
        ];

        foreach ($urls as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_navigation_groups_ladings_and_containers_with_export_and_import_items(): void
    {
        $this->assertSame('Bill of Ladings', ExportShipmentResource::getNavigationGroup());
        $this->assertSame('Export', ExportShipmentResource::getNavigationLabel());
        $this->assertSame('Bill of Ladings', ImportShipmentResource::getNavigationGroup());
        $this->assertSame('Import', ImportShipmentResource::getNavigationLabel());

        $this->assertSame('Containers', ExportContainerResource::getNavigationGroup());
        $this->assertSame('Export', ExportContainerResource::getNavigationLabel());
        $this->assertSame('Containers', ImportContainerResource::getNavigationGroup());
        $this->assertSame('Import', ImportContainerResource::getNavigationLabel());
    }

    public function test_navigation_group_order_is_ladings_then_containers(): void
    {
        $admin = $this->admin();

        // Read the rendered sidebar: every group wrapper carries its label in
        // data-group-label, in top-to-bottom order.
        $html = $this->actingAs($admin)
            ->get(ExportShipmentResource::getUrl('index'))
            ->assertOk()
            ->getContent();

        preg_match_all('/data-group-label="([^"]+)"/', (string) $html, $matches);

        $this->assertSame(
            ['Bill of Ladings', 'Containers', 'CRM', 'Master data', 'Monitoring'],
            $matches[1],
        );
    }

    public function test_admin_can_open_the_shipment_edit_pages(): void
    {
        $admin = $this->admin();

        $export = ExportShipment::query()->where('reference_number', 'REF-EXP-0001')->firstOrFail();
        $import = ImportShipment::query()->where('reference_number', 'REF-IMP-0001')->firstOrFail();

        $this->actingAs($admin)
            ->get(ExportShipmentResource::getUrl('edit', ['record' => $export]))
            ->assertOk()
            ->assertSee('Shipping Details')
            ->assertSee('Advance')
            ->assertSee('jumpToMilestone');

        $this->actingAs($admin)
            ->get(ImportShipmentResource::getUrl('edit', ['record' => $import]))
            ->assertOk()
            ->assertSee('Shipping Details')
            ->assertSee('Advance')
            ->assertSee('jumpToMilestone');
    }

    public function test_admin_can_create_shipments_with_just_the_customer_fields(): void
    {
        $admin = $this->admin();
        $company = Company::query()->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(CreateExportShipment::class)
            ->assertDontSee('Shipping Details')
            ->assertSee('Customer')
            ->assertDontSee('AJU number')
            ->fillForm([
                'company_id' => $company->getKey(),
                'document_received_date' => today()->toDateString(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $export = ExportShipment::query()->latest('id')->firstOrFail();
        $this->assertNotNull($export->reference_number);
        $this->assertSame($company->name, $export->company_name_snapshot);
        $this->assertSame(ExportMilestone::DocumentReceived, $export->current_milestone);

        Livewire::test(CreateImportShipment::class)
            ->fillForm([
                'company_id' => $company->getKey(),
                'document_received_date' => today()->toDateString(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $import = ImportShipment::query()->latest('id')->firstOrFail();
        $this->assertSame($company->name, $import->company_name_snapshot);
        $this->assertSame(ImportMilestone::DocumentReceived, $import->current_milestone);
    }

    public function test_admin_can_create_an_hs_code(): void
    {
        $admin = $this->admin();

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

    public function test_admin_can_attach_an_hs_code_on_the_import_shipment_form(): void
    {
        $admin = $this->admin();
        $hsCode = HsCode::query()->firstOrFail();

        // The cargo fields are milestone-gated, so the shipment must have
        // reached "checking document" before HS codes can be attached.
        $import = ImportShipment::query()->where('reference_number', 'REF-IMP-0001')->firstOrFail();
        $import->update(['current_milestone' => ImportMilestone::CheckingDocument]);

        $this->actingAs($admin);

        Livewire::test(EditImportShipment::class, ['record' => $import->getRouteKey()])
            ->fillForm(['hsCodes' => [$hsCode->getKey()]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($import->refresh()->hsCodes->contains($hsCode));
    }

    public function test_export_milestones_advance_regress_and_complete(): void
    {
        $export = ExportShipment::query()->where('reference_number', 'REF-EXP-0001')->firstOrFail();

        $this->assertSame(ExportMilestone::DocumentReceived, $export->current_milestone);

        $export->advanceMilestone();
        $this->assertSame(ExportMilestone::CheckingBookingOrder, $export->current_milestone);

        $export->regressMilestone();
        $this->assertSame(ExportMilestone::DocumentReceived, $export->current_milestone);
        $this->assertNull($export->previousMilestone());

        foreach (range(1, 10) as $ignored) {
            $export->advanceMilestone();
        }

        $this->assertSame(ExportMilestone::FinalChecking, $export->current_milestone);
        $this->assertSame(ShipmentStatus::Completed, $export->status);
        $this->assertNotNull($export->completed_at);

        $export->regressMilestone();
        $this->assertSame(ShipmentStatus::InProgress, $export->status);
        $this->assertNull($export->completed_at);
    }

    public function test_milestone_stepper_jumps_and_logs_each_change(): void
    {
        $export = ExportShipment::query()->where('reference_number', 'REF-EXP-0001')->firstOrFail();

        // Jumping forward skips the steps in between.
        $export->moveToMilestone(ExportMilestone::GateInCy);
        $this->assertSame(ExportMilestone::GateInCy, $export->current_milestone);

        // A milestone outside this process's sequence is ignored.
        $export->moveToMilestone(ImportMilestone::EmptyReturned);
        $this->assertSame(ExportMilestone::GateInCy, $export->current_milestone);

        // Jumping to the last milestone completes; jumping off it reopens.
        $export->moveToMilestone(ExportMilestone::FinalChecking);
        $this->assertSame(ShipmentStatus::Completed, $export->status);

        $export->moveToMilestone(ExportMilestone::DocumentReceived);
        $this->assertSame(ShipmentStatus::InProgress, $export->status);
        $this->assertNull($export->completed_at);

        // Each real change lands in the activity log (the ignored jump did not).
        $this->assertSame(3, $export->activityLogs()->where('event', 'milestone_changed')->count());
    }

    public function test_stepper_moves_at_most_one_step_forward(): void
    {
        $admin = $this->admin();

        $export = ExportShipment::query()->where('reference_number', 'REF-EXP-0001')->firstOrFail();
        $this->assertSame(ExportMilestone::DocumentReceived, $export->current_milestone);

        $this->actingAs($admin);

        // Two steps ahead is rejected — only the next step may be jumped to.
        Livewire::test(EditExportShipment::class, ['record' => $export->getRouteKey()])
            ->mountAction('jumpToMilestone', ['milestone' => 'pickup_empty_container'])
            ->callMountedAction();

        $this->assertSame(ExportMilestone::DocumentReceived, $export->refresh()->current_milestone);

        // One step forward works, and jumping back is unrestricted.
        Livewire::test(EditExportShipment::class, ['record' => $export->getRouteKey()])
            ->mountAction('jumpToMilestone', ['milestone' => 'checking_booking_order'])
            ->callMountedAction()
            ->mountAction('jumpToMilestone', ['milestone' => 'document_received'])
            ->callMountedAction();

        $this->assertSame(ExportMilestone::DocumentReceived, $export->refresh()->current_milestone);
    }

    public function test_import_milestones_skip_the_spjm_branch_unless_the_response_is_spjm(): void
    {
        $spjm = ImportShipment::query()->where('reference_number', 'REF-IMP-0001')->firstOrFail();
        $spjm->update(['current_milestone' => ImportMilestone::BillingResponseReceived]);
        $spjm->advanceMilestone();
        $this->assertSame(ImportMilestone::DocumentsUploaded, $spjm->current_milestone);

        $sppb = ImportShipment::query()->where('reference_number', 'REF-IMP-0002')->firstOrFail();
        $sppb->update(['current_milestone' => ImportMilestone::BillingResponseReceived]);
        $sppb->advanceMilestone();
        $this->assertSame(ImportMilestone::GateOutCy, $sppb->current_milestone);
    }

    public function test_company_list_links_customers_and_operators_to_their_edit_page(): void
    {
        $admin = $this->admin();

        $company = Company::query()->whereHas('users')->firstOrFail();
        $customer = $company->customers()->firstOrFail();

        $operator = User::factory()->create();
        $operator->assignRole(Role::OPERATOR);
        $company->users()->syncWithoutDetaching([$operator->getKey()]);

        $this->actingAs($admin)
            ->get(CompanyResource::getUrl('index'))
            ->assertOk()
            ->assertSee('Customers')
            ->assertSee('Operators')
            ->assertSee(UserResource::getUrl('edit', ['record' => $customer]), false)
            ->assertSee(UserResource::getUrl('edit', ['record' => $operator]), false);
    }

    public function test_company_form_keeps_operator_links_when_customers_change(): void
    {
        $admin = $this->admin();

        $company = Company::query()->whereHas('customers')->firstOrFail();

        $operator = User::factory()->create();
        $operator->assignRole(Role::OPERATOR);
        $company->users()->syncWithoutDetaching([$operator->getKey()]);

        $newCustomer = User::factory()->create();
        $newCustomer->assignRole(Role::CUSTOMER);

        $expectedOperators = array_map('intval', $company->operators()->pluck('users.id')->all());

        $this->actingAs($admin);

        $component = Livewire::test(EditCompany::class, ['record' => $company->getRouteKey()]);

        $this->assertEqualsCanonicalizing(
            $company->customers()->pluck('users.id')->all(),
            $component->get('data.customers'),
        );
        $this->assertEqualsCanonicalizing(
            $expectedOperators,
            array_map('intval', $component->get('data.operators')),
        );

        $component
            ->set('data.customers', [$newCustomer->getKey()])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(
            [$newCustomer->getKey()],
            array_map('intval', $company->customers()->pluck('users.id')->all()),
        );
        $this->assertSame(
            $expectedOperators,
            array_map('intval', $company->operators()->pluck('users.id')->all()),
        );
    }

    public function test_company_create_links_customers_and_operators(): void
    {
        $admin = $this->admin();

        $customer = User::factory()->create();
        $customer->assignRole(Role::CUSTOMER);

        $operator = User::factory()->create();
        $operator->assignRole(Role::OPERATOR);

        $this->actingAs($admin);

        Livewire::test(CreateCompany::class)
            ->fillForm([
                'name' => 'PT Link Test',
                'customers' => [$customer->getKey()],
                'operators' => [$operator->getKey()],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $company = Company::query()->where('name', 'PT Link Test')->firstOrFail();

        $this->assertSame(
            [$customer->getKey()],
            array_map('intval', $company->customers()->pluck('users.id')->all()),
        );
        $this->assertSame(
            [$operator->getKey()],
            array_map('intval', $company->operators()->pluck('users.id')->all()),
        );
    }

    public function test_container_repeater_header_shows_the_container_number(): void
    {
        $admin = $this->admin();

        $export = ExportShipment::query()->where('reference_number', 'REF-EXP-0001')->firstOrFail();
        $container = $export->containers()->firstOrFail();

        $this->actingAs($admin);

        $component = Livewire::test(EditExportShipment::class, ['record' => $export->getRouteKey()]);

        // Existing containers show their number in the collapsed item header.
        $this->assertMatchesRegularExpression(
            '/fi-fo-repeater-item-header-label[^>]*>\s*'.preg_quote($container->container_number, '/').'\s*</s',
            $component->html(),
        );

        // Renaming the container updates the header label.
        $component
            ->set("data.containers.record-{$container->getKey()}.container_number", 'TESTU7654321');

        $this->assertMatchesRegularExpression(
            '/fi-fo-repeater-item-header-label[^>]*>\s*TESTU7654321\s*</s',
            $component->html(),
        );
    }

    public function test_company_edit_page_lists_only_the_companys_shipments(): void
    {
        $admin = $this->admin();

        $company = Company::query()->whereHas('exportShipments')->firstOrFail();
        $ownExport = $company->exportShipments()->first();
        $otherExport = ExportShipment::query()->whereKeyNot($company->exportShipments()->pluck('id'))->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(ExportShipmentsRelationManager::class, [
            'ownerRecord' => $company,
            'pageClass' => EditCompany::class,
        ])
            ->assertCanSeeTableRecords([$ownExport])
            ->assertCanNotSeeTableRecords([$otherExport]);

        $importCompany = Company::query()->whereHas('importShipments')->firstOrFail();
        $ownImport = $importCompany->importShipments()->first();
        $otherImport = ImportShipment::query()->whereKeyNot($importCompany->importShipments()->pluck('id'))->firstOrFail();

        Livewire::test(ImportShipmentsRelationManager::class, [
            'ownerRecord' => $importCompany,
            'pageClass' => EditCompany::class,
        ])
            ->assertCanSeeTableRecords([$ownImport])
            ->assertCanNotSeeTableRecords([$otherImport]);
    }

    public function test_user_list_links_company_names_to_their_edit_page(): void
    {
        $admin = $this->admin();

        $user = User::query()->whereHas('companies')->firstOrFail();
        $company = $user->companies()->firstOrFail();

        $this->actingAs($admin)
            ->get(UserResource::getUrl('index'))
            ->assertOk()
            ->assertSee('Companies')
            ->assertSee($company->name)
            ->assertSee(CompanyResource::getUrl('edit', ['record' => $company]), false);
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
