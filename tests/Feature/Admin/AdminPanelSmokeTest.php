<?php

/**
 * File: tests/Feature/Admin/AdminPanelSmokeTest.php
 * Responsibility: Guards the admin panel against runtime errors.
 * What it does:
 * - Seeds the database and, as an admin, opens every resource list and edit page.
 * - Covers shipment creation, HS codes, milestone transitions, the stepper and
 *   the company relation managers.
 * - Guards container lists against a shipment whose B/L number is still null.
 * - Checks B/L tables list container numbers as links to the container edit page.
 * How to use: `php artisan test --filter=AdminPanelSmokeTest`.
 * How to extend: Add create/edit page assertions as resources grow.
 */

namespace Tests\Feature\Admin;

use App\Enums\BillingResponse;
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
use App\Filament\Resources\ExportContainers\Pages\ListExportContainers;
use App\Filament\Resources\ExportShipments\ExportShipmentResource;
use App\Filament\Resources\ExportShipments\Pages\CreateExportShipment;
use App\Filament\Resources\ExportShipments\Pages\EditExportShipment;
use App\Filament\Resources\ExportShipments\Pages\ListExportShipments;
use App\Filament\Resources\HsCodes\HsCodeResource;
use App\Filament\Resources\HsCodes\Pages\CreateHsCode;
use App\Filament\Resources\ImportContainers\ImportContainerResource;
use App\Filament\Resources\ImportContainers\Pages\CreateImportContainer;
use App\Filament\Resources\ImportContainers\Pages\ListImportContainers;
use App\Filament\Resources\ImportShipments\ImportShipmentResource;
use App\Filament\Resources\ImportShipments\Pages\CreateImportShipment;
use App\Filament\Resources\ImportShipments\Pages\EditImportShipment;
use App\Filament\Resources\ImportShipments\Pages\ListImportShipments;
use App\Filament\Resources\Users\UserResource;
use App\Models\Company;
use App\Models\ExportShipment;
use App\Models\HsCode;
use App\Models\ImportContainer;
use App\Models\ImportShipment;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
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

    public function test_container_lists_survive_a_shipment_without_a_bl_number(): void
    {
        $admin = $this->admin();

        ExportShipment::query()->first()->update(['bl_number' => null]);
        ImportShipment::query()->first()->update(['bl_number' => null]);

        $this->actingAs($admin);

        Livewire::test(ListExportContainers::class)->assertOk();
        Livewire::test(ListImportContainers::class)->assertOk();
    }

    public function test_bill_of_lading_tables_link_each_container_number(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $export = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();
        $exportContainer = $export->containers()->firstOrFail();

        Livewire::test(ListExportShipments::class)
            ->assertSee($exportContainer->container_number)
            ->assertSee(ExportContainerResource::getUrl('edit', ['record' => $exportContainer]), false);

        $import = ImportShipment::query()->where('bl_number', 'BL-IMP-0001')->firstOrFail();
        $importContainer = $import->containers()->firstOrFail();

        Livewire::test(ListImportShipments::class)
            ->assertSee($importContainer->container_number)
            ->assertSee(ImportContainerResource::getUrl('edit', ['record' => $importContainer]), false);
    }

    public function test_unlocked_empty_fields_carry_the_attention_marker(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $export = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();

        // At step 1 every process field is locked, so nothing is marked.
        $export->update(['current_milestone' => ExportMilestone::DocumentReceived]);

        Livewire::test(EditExportShipment::class, ['record' => $export->getRouteKey()])
            ->assertDontSee('bl-empty-field', false);

        // At step 3 the step-2 fields are editable; the empty ones (e.g. DO
        // number, depot/CY closing times) are marked for attention.
        $export->update(['current_milestone' => ExportMilestone::PickupEmptyContainer]);

        Livewire::test(EditExportShipment::class, ['record' => $export->getRouteKey()])
            ->assertSee('bl-empty-field', false);
    }

    public function test_edit_pages_offer_a_new_bill_of_lading_header_action(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $export = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();

        Livewire::test(EditExportShipment::class, ['record' => $export->getRouteKey()])
            ->assertActionExists('createShipment')
            ->assertActionHasUrl('createShipment', CreateExportShipment::getUrl());

        $import = ImportShipment::query()->where('bl_number', 'BL-IMP-0001')->firstOrFail();

        Livewire::test(EditImportShipment::class, ['record' => $import->getRouteKey()])
            ->assertActionExists('createShipment')
            ->assertActionHasUrl('createShipment', CreateImportShipment::getUrl());
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

    public function test_the_import_edit_page_flags_customer_notes(): void
    {
        $admin = $this->admin();
        $import = ImportShipment::query()->where('bl_number', 'BL-IMP-0001')->firstOrFail();
        $customer = User::query()->where('email', 'customer@example.com')->firstOrFail();

        $this->actingAs($admin);

        // Only office notes so far: the flag stays hidden.
        $import->notes()->create(['body' => 'Office note', 'author_id' => $admin->getKey()]);

        Livewire::test(EditImportShipment::class, ['record' => $import->getRouteKey()])
            ->assertDontSee('bl-note-flag', false);

        // A note authored by a customer account raises it, with a link that
        // jumps to the Notes tab.
        $import->notes()->create([
            'body' => 'Please change the HS code.',
            'author_id' => $customer->getKey(),
        ]);

        Livewire::test(EditImportShipment::class, ['record' => $import->getRouteKey()])
            ->assertSee('The customer sent you a note.')
            ->assertSee('data-bl-tab-goto="Notes"', false);
    }

    public function test_the_confirmation_checklist_records_who_confirmed(): void
    {
        $admin = $this->admin();
        // BL-IMP-0001 sits past Step 5, so the toggle is editable.
        $import = ImportShipment::query()->where('bl_number', 'BL-IMP-0001')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(EditImportShipment::class, ['record' => $import->getRouteKey()])
            ->assertDontSee('Confirmed by')
            ->fillForm(['confirmation_checklist' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($admin->getKey(), $import->refresh()->confirmed_by);

        Livewire::test(EditImportShipment::class, ['record' => $import->getRouteKey()])
            ->assertSee('Confirmed by '.$admin->name);

        // Unchecking clears the stamp.
        Livewire::test(EditImportShipment::class, ['record' => $import->getRouteKey()])
            ->fillForm(['confirmation_checklist' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertNull($import->refresh()->confirmed_by);
        $this->assertFalse($import->confirmation_checklist);
    }

    public function test_admin_can_open_the_shipment_edit_pages(): void
    {
        $admin = $this->admin();

        $export = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();
        $import = ImportShipment::query()->where('bl_number', 'BL-IMP-0001')->firstOrFail();

        $this->actingAs($admin)
            ->get(ExportShipmentResource::getUrl('edit', ['record' => $export]))
            ->assertOk()
            ->assertSee('Shipping Details')
            ->assertSee('Status')
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
        // reached "waiting process bahandle" before HS codes can be attached.
        $import = ImportShipment::query()->where('bl_number', 'BL-IMP-0001')->firstOrFail();
        $import->update(['current_milestone' => ImportMilestone::WaitingProcessBehandle]);

        $this->actingAs($admin);

        Livewire::test(EditImportShipment::class, ['record' => $import->getRouteKey()])
            ->fillForm(['hsCodes' => [$hsCode->getKey()]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($import->refresh()->hsCodes->contains($hsCode));
    }

    public function test_export_milestones_advance_regress_and_complete(): void
    {
        $export = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();
        // The seeder lands demo shipments on their data's step; rewind this
        // one so the walk from the first step is what gets tested.
        $export->update(['current_milestone' => ExportMilestone::DocumentReceived]);

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

    public function test_a_draft_shipment_publishes_when_it_advances(): void
    {
        $export = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();
        $export->update(['status' => ShipmentStatus::Draft]);

        // Advancing past "document received" publishes it to the portal.
        $export->advanceMilestone();
        $this->assertSame(ShipmentStatus::InProgress, $export->status);

        // A cancelled shipment keeps its status when the milestone moves.
        $export->update(['status' => ShipmentStatus::Cancelled]);
        $export->advanceMilestone();
        $this->assertSame(ShipmentStatus::Cancelled, $export->status);
    }

    public function test_milestone_stepper_jumps_and_logs_each_change(): void
    {
        $export = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();
        $loggedBefore = $export->activityLogs()->where('event', 'milestone_changed')->count();

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
        $this->assertSame($loggedBefore + 3, $export->activityLogs()->where('event', 'milestone_changed')->count());
    }

    public function test_stepper_moves_at_most_one_step_forward(): void
    {
        $admin = $this->admin();

        $export = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();
        // The seeder lands demo shipments on their data's step; rewind this
        // one so the one-step-forward rule is tested from the start.
        $export->update(['current_milestone' => ExportMilestone::DocumentReceived]);
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
        $spjm = ImportShipment::query()->where('bl_number', 'BL-IMP-0001')->firstOrFail();
        $spjm->update(['current_milestone' => ImportMilestone::ResponseBilling]);
        $spjm->advanceMilestone();
        $this->assertSame(ImportMilestone::UploadAllDocument, $spjm->current_milestone);

        $sppb = ImportShipment::query()->where('bl_number', 'BL-IMP-0002')->firstOrFail();
        $sppb->update(['current_milestone' => ImportMilestone::ResponseBilling]);
        $sppb->advanceMilestone();
        $this->assertSame(ImportMilestone::ContainerShippingSchedule, $sppb->current_milestone);
    }

    public function test_choosing_spjm_clamps_a_milestone_that_skipped_the_branch(): void
    {
        $shipment = ImportShipment::query()->where('bl_number', 'BL-IMP-0012')->firstOrFail();

        // Saved as non-SPJM sitting on the branch point.
        $shipment->forceFill([
            'billing_response' => BillingResponse::Ap,
            'current_milestone' => ImportMilestone::ResponseBilling,
        ])->save();

        // Picking SPJM while the milestone already jumped past the branch must
        // pull the milestone back to Response billing, not skip the SPJM steps.
        $shipment->billing_response = BillingResponse::Spjm;
        $shipment->current_milestone = ImportMilestone::ContainerShippingSchedule;
        $shipment->save();

        $this->assertSame(ImportMilestone::ResponseBilling, $shipment->refresh()->current_milestone);
    }

    public function test_leaving_spjm_clamps_a_milestone_on_an_spjm_only_step(): void
    {
        $shipment = ImportShipment::query()->where('bl_number', 'BL-IMP-0012')->firstOrFail();

        $shipment->forceFill([
            'billing_response' => BillingResponse::Spjm,
            'current_milestone' => ImportMilestone::WaitingProcessBehandle,
        ])->save();

        // Switching off SPJM orphans the SPJM-only step, so reset to the branch.
        $shipment->billing_response = BillingResponse::Sppb;
        $shipment->save();

        $this->assertSame(ImportMilestone::ResponseBilling, $shipment->refresh()->current_milestone);
    }

    public function test_stepper_jump_uses_the_live_billing_response(): void
    {
        $admin = $this->admin();

        $shipment = ImportShipment::query()->where('bl_number', 'BL-IMP-0012')->firstOrFail();
        $shipment->forceFill([
            'billing_response' => BillingResponse::Ap,
            'current_milestone' => ImportMilestone::ResponseBilling,
        ])->save();

        $this->actingAs($admin);

        // With SPJM selected in the form (not yet saved), jumping straight to
        // Container shipping schedule must be refused.
        Livewire::test(EditImportShipment::class, ['record' => $shipment->getRouteKey()])
            ->set('data.billing_response', BillingResponse::Spjm->value)
            ->mountAction('jumpToMilestone', ['milestone' => ImportMilestone::ContainerShippingSchedule->value])
            ->callMountedAction();

        $this->assertSame(ImportMilestone::ResponseBilling, $shipment->refresh()->current_milestone);

        // A non-SPJM response allows the same jump.
        Livewire::test(EditImportShipment::class, ['record' => $shipment->getRouteKey()])
            ->set('data.billing_response', BillingResponse::Ap->value)
            ->mountAction('jumpToMilestone', ['milestone' => ImportMilestone::ContainerShippingSchedule->value])
            ->callMountedAction();

        $this->assertSame(ImportMilestone::ContainerShippingSchedule, $shipment->refresh()->current_milestone);
    }

    public function test_stepper_rows_hold_at_most_ten_steps(): void
    {
        // The SPJM import sequence has 21 steps: 10 + (response billing + the
        // five SPJM-only steps) + 5.
        $html = view('filament.bill-of-ladings.milestone-stepper', [
            'sequence' => ImportMilestone::sequence(BillingResponse::Spjm),
            'current' => ImportMilestone::DocumentReceived,
            'editable' => true,
        ])->render();

        $rows = explode('</ol>', $html);

        $this->assertSame(3, substr_count($html, '<ol>'));
        $this->assertSame(21, substr_count($html, 'data-bl-ms-step='));
        $this->assertSame(10, substr_count($rows[0], '<li'));
        $this->assertSame(6, substr_count($rows[1], '<li'));
        // The five SPJM-only steps are red.
        $this->assertSame(5, substr_count($html, ' spjm'));

        // Without the SPJM response the branch is absent: 10 + 6 steps, no red.
        $sppbHtml = view('filament.bill-of-ladings.milestone-stepper', [
            'sequence' => ImportMilestone::sequence(BillingResponse::Sppb),
            'current' => ImportMilestone::DocumentReceived,
            'editable' => true,
        ])->render();

        $this->assertSame(2, substr_count($sppbHtml, '<ol>'));
        $this->assertSame(16, substr_count($sppbHtml, 'data-bl-ms-step='));
        $this->assertSame(0, substr_count($sppbHtml, ' spjm'));
    }

    public function test_import_container_tab_header_fields_follow_the_milestones(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        // BL-IMP-0001 sits at "waiting process bahandle": the cargo details
        // are unlocked, the loading data still waits for "container shipping
        // schedule".
        $import = ImportShipment::query()->where('bl_number', 'BL-IMP-0001')->firstOrFail();

        Livewire::test(EditImportShipment::class, ['record' => $import->getRouteKey()])
            ->assertFormFieldExists('goods_description')
            ->assertFormFieldExists('packages')
            ->assertFormFieldExists('hsCodes')
            ->assertFormFieldExists('terminal_name')
            ->assertFormFieldExists('loading_date')
            ->assertFormFieldExists('loading_destination')
            ->assertSee('Locked until Step 17: Container shipping schedule');
    }

    public function test_document_received_by_offers_staff_only(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        // The scope behind the picker excludes customer accounts.
        $internalEmails = User::query()->internal()->pluck('email');
        $this->assertTrue($internalEmails->contains('admin@example.com'));
        $this->assertTrue($internalEmails->contains('operator@example.com'));
        $this->assertFalse($internalEmails->contains('customer@example.com'));
        $this->assertFalse($internalEmails->contains('sari@java-retail.test'));

        $customerIds = User::query()->whereNotIn('email', $internalEmails->all())->pluck('id')->map(fn ($id): int => (int) $id)->all();

        $export = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();

        Livewire::test(EditExportShipment::class, ['record' => $export->getRouteKey()])
            ->assertFormFieldExists('document_received_by', function (Select $field) use ($customerIds): bool {
                $optionIds = array_map('intval', array_keys($field->getOptions()));

                return $optionIds !== [] && array_intersect($optionIds, $customerIds) === [];
            });
    }

    public function test_spjm_demo_shipments_cover_the_branch(): void
    {
        $completed = ImportShipment::query()->where('bl_number', 'BL-IMP-0003')->firstOrFail();
        $this->assertSame(BillingResponse::Spjm, $completed->billing_response);
        $this->assertSame(ImportMilestone::EmptyReturned, $completed->current_milestone);
        $this->assertSame(ShipmentStatus::Completed, $completed->status);
        $this->assertSame(2, $completed->containers()->count());

        $fresh = ImportShipment::query()->where('bl_number', 'BL-IMP-0004')->firstOrFail();
        $this->assertSame(BillingResponse::Spjm, $fresh->billing_response);
        $this->assertSame(ImportMilestone::ResponseBilling, $fresh->current_milestone);
        $this->assertSame(2, $fresh->containers()->count());
        // The size unlocks later in the branch, so the fresh containers have none yet.
        $this->assertNull($fresh->containers()->first()->size);
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

        $export = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();
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

    public function test_import_form_uses_the_tracking_url_and_step_11_cargo_fields(): void
    {
        $admin = $this->admin();

        $import = ImportShipment::query()->where('bl_number', 'BL-IMP-0001')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(EditImportShipment::class, ['record' => $import->getRouteKey()])
            ->assertOk()
            ->assertSee('Tracking position (url)')
            ->assertDontSee('Tracking position input (manual)')
            ->assertSee('Description of goods')
            ->assertSee('HS codes');
    }

    public function test_new_standalone_container_inherits_the_shipment_cargo_and_hs_codes(): void
    {
        $admin = $this->admin();

        $import = ImportShipment::query()->where('bl_number', 'BL-IMP-0001')->firstOrFail();
        $shipmentHsCodeIds = $import->hsCodes()->pluck('hs_codes.id')->sort()->values()->all();

        $this->assertNotEmpty($shipmentHsCodeIds);

        $this->actingAs($admin);

        Livewire::test(CreateImportContainer::class)
            ->fillForm([
                'import_shipment_id' => $import->getKey(),
                'container_number' => 'INHERIT-CONT-1',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $container = ImportContainer::query()->where('container_number', 'INHERIT-CONT-1')->firstOrFail();

        $this->assertSame($import->goods_description, $container->description_of_goods);
        $this->assertSame($import->packages, $container->packages);
        $this->assertSame(
            $shipmentHsCodeIds,
            $container->hsCodes()->pluck('hs_codes.id')->sort()->values()->all(),
        );
    }

    public function test_new_repeater_container_persists_its_cargo_and_hs_codes(): void
    {
        $admin = $this->admin();

        $import = ImportShipment::query()->where('bl_number', 'BL-IMP-0001')->firstOrFail();
        $shipmentHsCodeIds = $import->hsCodes()->pluck('hs_codes.id')->sort()->values()->all();

        $this->assertNotEmpty($shipmentHsCodeIds);

        $this->actingAs($admin);

        $component = Livewire::test(EditImportShipment::class, ['record' => $import->getRouteKey()]);

        $containers = $component->get('data.containers');
        $containers['new-1'] = [
            'container_number' => 'REPEATER-CONT-1',
            'import_shipment_id' => $import->getKey(),
            'factory_loading_status' => 'on_process',
            'description_of_goods' => $import->goods_description,
            'packages' => $import->packages,
            'hsCodes' => $shipmentHsCodeIds,
        ];

        $component->set('data.containers', $containers)->call('save')->assertHasNoFormErrors();

        $container = ImportContainer::query()->where('container_number', 'REPEATER-CONT-1')->firstOrFail();

        $this->assertSame($import->goods_description, $container->description_of_goods);
        $this->assertSame($import->packages, $container->packages);
        $this->assertSame(
            $shipmentHsCodeIds,
            $container->hsCodes()->pluck('hs_codes.id')->sort()->values()->all(),
        );
    }

    public function test_add_container_action_appends_a_row_and_seeds_its_cargo(): void
    {
        $admin = $this->admin();

        $import = ImportShipment::query()->where('bl_number', 'BL-IMP-0001')->firstOrFail();
        $shipmentHsCodeIds = $import->hsCodes()->pluck('hs_codes.id')->sort()->values()->all();

        $this->assertNotEmpty($shipmentHsCodeIds);

        $this->actingAs($admin);

        $component = Livewire::test(EditImportShipment::class, ['record' => $import->getRouteKey()]);

        $initialCount = count($component->get('data.containers'));

        // The repeater's add action must append a row (a custom seed callback
        // previously replaced the append step, so nothing was added).
        $component
            ->call('mountAction', 'add', [], ['recordKey' => $import->getKey(), 'schemaComponent' => 'form.containers'])
            ->call('callMountedAction')
            ->assertHasNoFormErrors();

        $containers = $component->get('data.containers');

        $this->assertCount($initialCount + 1, $containers);

        $key = array_key_last($containers);

        $this->assertSame($import->goods_description, $containers[$key]['description_of_goods'] ?? null);
        $this->assertSame($import->packages, $containers[$key]['packages'] ?? null);
        $this->assertSame(
            $shipmentHsCodeIds,
            collect($containers[$key]['hsCodes'] ?? [])->map(fn ($id): int => (int) $id)->sort()->values()->all(),
        );

        $component
            ->set("data.containers.{$key}.container_number", 'ADD-ACTION-CONT-1')
            ->call('save')
            ->assertHasNoFormErrors();

        $container = ImportContainer::query()->where('container_number', 'ADD-ACTION-CONT-1')->firstOrFail();

        $this->assertSame($import->goods_description, $container->description_of_goods);
        $this->assertSame($import->packages, $container->packages);
        $this->assertSame(
            $shipmentHsCodeIds,
            $container->hsCodes()->pluck('hs_codes.id')->sort()->values()->all(),
        );
    }
}
