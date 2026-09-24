<?php

/**
 * File: tests/Feature/Portal/PortalTest.php
 * Responsibility: Verifies the customer portal behaviour from spec.md.
 * What it does:
 * - Covers login routing, the no-registration OTP rule, the signed verify step
 *   (including posting through the rendered form), the attempt rate limiter,
 *   the Export/Import dashboard tabs, per-customer scoping, shipment/container
 *   search, Import/Export container progress steps, row chips, the
 *   sailing information card, the POD / Vessel arrival dashboard column and
 *   the import PIB confirmation and revision notes.
 * - Also asserts the seeded many-to-many between companies and portal users.
 * How to use: `php artisan test --filter=PortalTest`.
 * How to extend: add a test per new portal screen or rule.
 */

namespace Tests\Feature\Portal;

use App\Enums\BillingResponse;
use App\Enums\ContainerStatus;
use App\Enums\ExportMilestone;
use App\Enums\ImportMilestone;
use App\Enums\ShipmentStatus;
use App\Livewire\Customer\Dashboard;
use App\Livewire\Customer\ExportShipmentDetail;
use App\Livewire\Customer\ImportShipmentDetail;
use App\Models\ActivityLog;
use App\Models\Attachment;
use App\Models\Company;
use App\Models\ExportShipment;
use App\Models\HsCode;
use App\Models\ImportShipment;
use App\Models\Role;
use App\Models\User;
use App\Services\ShipmentTimeline;
use App\Services\ShipmentTimelineEntry;
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

        // Default is All: both processes appear in one combined list.
        Livewire::test(Dashboard::class)
            ->assertSee('Type')
            ->assertSet('type', '')
            ->assertSee('BL-EXP-0001')
            ->assertSee('BL-IMP-0001')
            ->set('type', 'export')
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
            ->assertSee('POD / Vessel arrival')
            ->assertSee('Document received date')
            ->assertDontSee('Latest place')
            ->assertSee('Final checking shipment details');
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
            ->set('type', 'export')
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

    public function test_an_admin_may_open_any_customers_shipment(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $export = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();
        $exportContainer = $export->containers()->firstOrFail();
        $import = ImportShipment::query()->where('bl_number', 'BL-IMP-0001')->firstOrFail();
        $importContainer = $import->containers()->firstOrFail();

        $this->actingAs($admin)
            ->get(route('customer.export-shipments.show', ['exportShipment' => $export]))
            ->assertOk()
            ->assertSee('BL-EXP-0001')
            ->assertSee($exportContainer->container_number);

        $this->actingAs($admin)
            ->get(route('customer.import-shipments.show', ['importShipment' => $import]))
            ->assertOk()
            ->assertSee('BL-IMP-0001')
            ->assertSee($importContainer->container_number);
    }

    public function test_draft_shipments_are_hidden_from_the_portal(): void
    {
        $user = $this->portalUser();
        $company = $user->companies()->first();

        $active = $this->exportShipmentFor($company, 'BL-PORTAL-ACTIVE');
        $draft = $this->exportShipmentFor($company, 'BL-PORTAL-DRAFT', ShipmentStatus::Draft);

        $this->assertNotSame($active->getKey(), $draft->getKey());

        $this->actingAs($user)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('BL-PORTAL-ACTIVE')
            ->assertDontSee('BL-PORTAL-DRAFT');

        // Drafts stay closed by URL too.
        $this->actingAs($user)
            ->get(route('customer.export-shipments.show', ['exportShipment' => $draft->getKey()]))
            ->assertNotFound();

        // Admins browse the portal as the customer sees it, so drafts stay hidden.
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertDontSee('BL-PORTAL-DRAFT');

        // Advancing past the first milestone publishes it.
        $draft->advanceMilestone();

        $this->actingAs($user)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('BL-PORTAL-DRAFT');
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

    public function test_shipment_pages_render_container_details_without_new_tab_links(): void
    {
        $user = $this->portalUser();
        $company = $user->companies()->firstOrFail();
        $exportShipment = $this->exportShipmentFor($company, 'BL-EXP-ACCORDION');
        $importShipment = $this->importShipmentFor($company, 'BL-IMP-ACCORDION');
        $exportContainer = $exportShipment->containers()->firstOrFail();
        $importContainer = $importShipment->containers()->firstOrFail();

        $this->actingAs($user);

        Livewire::test(ExportShipmentDetail::class, ['exportShipment' => $exportShipment->getKey()])
            ->assertOk()
            ->assertSee('<details', false)
            ->assertSee('<summary', false)
            ->assertSee($exportContainer->container_number)
            ->assertDontSee('target="_blank"', false);

        Livewire::test(ImportShipmentDetail::class, ['importShipment' => $importShipment->getKey()])
            ->assertOk()
            ->assertSee('<details', false)
            ->assertSee('<summary', false)
            ->assertSee($importContainer->container_number)
            ->assertDontSee('target="_blank"', false);
    }

    public function test_shipment_accordions_render_matching_admin_fields_and_visible_photos(): void
    {
        $user = $this->portalUser();
        $company = $user->companies()->firstOrFail();
        $exportShipment = $this->exportShipmentFor($company, 'BL-EXP-FIELD-PARITY');
        $importShipment = $this->importShipmentFor($company, 'BL-IMP-FIELD-PARITY');
        $exportContainer = $exportShipment->containers()->firstOrFail();
        $importContainer = $importShipment->containers()->firstOrFail();
        $hsCode = HsCode::query()->firstOrFail();

        $importContainer->update([
            'size' => '40',
            'gross_weight' => '1020.500',
            'cbm' => '8.250',
            'description_of_goods' => 'Consumer electronics',
            'packages' => '85 cartons',
            'driver_name' => 'Rina Driver',
            'license_number' => 'B 1234 ABC',
            'gate_out_cy_at' => '2026-09-23 09:15:00',
            'tracking_position' => 'Bekasi',
            'tracking_position_url' => 'https://tracking.example.test/import',
            'factory_loading_status' => 'finished',
            'return_depot_name' => 'Jakarta Return Depot',
            'empty_returned_at' => '2026-09-23 18:45:00',
            'status' => 'completed',
            'completed_at' => '2026-09-23 19:00:00',
        ]);
        $importContainer->hsCodes()->attach($hsCode);

        $exportContainer->update([
            'size' => '20',
            'seal_number' => 'SEAL-EXP-22',
            'driver_name' => 'Agus Driver',
            'license_number' => 'B 7777 EF',
            'driver_license_number' => 'SIM-987',
            'tracking_position' => 'Tanjung Priok',
            'tracking_position_url' => 'https://tracking.example.test/export',
            'stuffing_status' => 'finished',
            'port_of_loading' => 'Tanjung Priok',
            'gate_in_cy_at' => '2026-09-23 08:30:00',
            'vgm_value' => '1234.500',
            'final_checked' => true,
            'final_checked_at' => '2026-09-23 11:45:00',
        ]);

        // At the final milestone every container step is reached, so each
        // step's fields render.
        $importShipment->update(['current_milestone' => ImportMilestone::EmptyReturned]);
        $exportShipment->update(['current_milestone' => ExportMilestone::FinalChecking]);

        $createPhoto = fn (array $attributes): Attachment => Attachment::query()->create([
            'disk' => 'public',
            'directory' => 'portal-test',
            'visibility' => 'public',
            'name' => 'portal-test-photo.jpg',
            'path' => 'portal-test/photo.jpg',
            'type' => 'image/jpeg',
            'ext' => 'jpg',
        ] + $attributes);

        $createPhoto([
            'alt' => 'Visible import door photo',
            'import_container_id' => $importContainer->getKey(),
            'category' => 'door_photo',
            'is_customer_visible' => true,
        ]);
        $createPhoto([
            'alt' => 'Internal import floor photo',
            'import_container_id' => $importContainer->getKey(),
            'category' => 'floor_photo',
            'is_customer_visible' => false,
        ]);
        $createPhoto([
            'alt' => 'Visible export seal photo',
            'export_container_id' => $exportContainer->getKey(),
            'category' => 'seal_photo',
            'is_customer_visible' => true,
        ]);

        $this->actingAs($user);

        $importPage = Livewire::test(ImportShipmentDetail::class, ['importShipment' => $importShipment->getKey()]);
        foreach ([
            '40 ft', '1020.500', 'Description of goods',
            'Driver name', 'Rina Driver', 'No. License', 'B 1234 ABC', 'Gate out CY', 'Tracking position driver',
            'Bekasi', 'Tracking position (url)', 'Open tracking link',
            'Loading in factory status', 'Finished', 'Return depot name', 'Jakarta Return Depot', 'Return date',
            'Photo Door', 'Visible import door photo',
            'Cargo tracking', 'Completed',
            'Gross weight 1020.500 kg',
            'Container shipping schedule', 'Gate out from inbound terminal', 'Container on the way factory',
            'Container arrived in factory', 'Empty container returned',
        ] as $fieldOrValue) {
            $importPage->assertSee($fieldOrValue);
        }
        $importPage->assertDontSee('Internal import floor photo')->assertDontSee('VGM (kg)');

        $exportPage = Livewire::test(ExportShipmentDetail::class, ['exportShipment' => $exportShipment->getKey()]);
        foreach ([
            '20 ft', 'SEAL-EXP-22', 'Driver name', 'Agus Driver',
            'Vehicle / Truck Number', 'B 7777 EF', 'Driver License Number', 'SIM-987', 'Tracking position',
            'Tanjung Priok', 'Tracking position (url)', 'Open tracking link', 'Stuffing status at Factory',
            'Finished', 'Port of loading', 'Gate in CY at', 'VGM (kg)', '1234.500',
            'Final checked', 'Yes', 'Final checked at', 'Photo Seal', 'Visible export seal photo',
            'Cargo tracking', 'Completed',
            'VGM 1234.500 kg',
            'Pick up empty container at depot', 'Container on the way to factory',
            'Checking PEB & NPE', 'Gate in CY', 'Final checking shipment details',
        ] as $fieldOrValue) {
            $exportPage->assertSee($fieldOrValue);
        }
        $exportPage->assertDontSee('Gross weight (kg)');
    }

    public function test_container_steps_mark_the_current_milestone_and_hide_future_fields(): void
    {
        $user = $this->portalUser();
        $shipment = $this->exportShipmentFor($user->companies()->first(), 'BL-EXP-STEPS');
        $container = $shipment->containers()->firstOrFail();

        $container->update([
            'driver_name' => 'Agus Driver',
            'tracking_position' => 'Tanjung Priok',
            'stuffing_status' => 'on_process',
            'vgm_value' => '1234.500',
        ]);
        $shipment->update(['current_milestone' => ExportMilestone::StuffingPebNpe]);

        $progress = app(ShipmentTimeline::class)
            ->forContainers($shipment->refresh(), [$container->refresh()])[$container->getKey()];

        $this->assertSame([
            'Pick up empty container at depot',
            'Container on the way to factory',
            'Stuffing at factory / PEB & NPE',
            'Checking PEB & NPE',
            'Gate in CY',
            'Final checking shipment details',
        ], array_map(fn (ShipmentTimelineEntry $step) => $step->title, $progress->steps));

        $this->assertFalse($progress->steps[0]->isPending);
        $this->assertFalse($progress->steps[1]->isPending);
        $this->assertTrue($progress->steps[2]->isLatest);
        $this->assertSame('Stuffing at factory / PEB & NPE', $progress->current()->title);
        $this->assertSame('Container on the way to factory', $progress->steps[1]->title);
        $this->assertSame('Agus Driver', collect($progress->steps[0]->fields)->firstWhere('label', 'Driver name')['value']);
        $this->assertSame([], $progress->steps[3]->fields);
        $this->assertSame([], $progress->steps[4]->fields);
        $this->assertSame([], $progress->steps[5]->fields);
        $this->assertSame(3, $progress->reachedCount());
        $this->assertSame(6, $progress->totalSteps());
        $this->assertSame(ContainerStatus::InProgress, $progress->status);

        $this->actingAs($user);

        Livewire::test(ExportShipmentDetail::class, ['exportShipment' => $shipment->getKey()])
            ->assertOk()
            ->assertSee('Stuffing at factory / PEB & NPE')
            ->assertSee('aria-current="step"', false)
            ->assertDontSee('1234.500');
    }

    public function test_container_steps_follow_the_spjm_branch(): void
    {
        $user = $this->portalUser();
        $company = $user->companies()->firstOrFail();

        $spjm = $this->importShipmentFor($company, 'BL-IMP-SPJM');
        // Saving the response and a post-branch milestone in one save would
        // clamp the milestone back to Response billing, so the response goes
        // first and the milestone second.
        $spjm->update(['billing_response' => BillingResponse::Spjm]);
        $spjm->update(['current_milestone' => ImportMilestone::GateOutCy]);

        $plain = $this->importShipmentFor($company, 'BL-IMP-PLAIN');
        $plain->update(['current_milestone' => ImportMilestone::GateOutCy]);

        $timeline = app(ShipmentTimeline::class);
        $spjmContainer = $spjm->containers()->firstOrFail();
        $plainContainer = $plain->containers()->firstOrFail();

        $spjmTitles = array_map(
            fn (ShipmentTimelineEntry $step) => $step->title,
            $timeline->forContainers($spjm->refresh(), [$spjmContainer])[$spjmContainer->getKey()]->steps,
        );
        $plainTitles = array_map(
            fn (ShipmentTimelineEntry $step) => $step->title,
            $timeline->forContainers($plain->refresh(), [$plainContainer])[$plainContainer->getKey()]->steps,
        );

        $this->assertSame('Container inspection', $spjmTitles[0]);
        $this->assertSame('Container shipping schedule', $plainTitles[0]);
        $this->assertNotContains('Container inspection', $plainTitles);
    }

    public function test_a_shipment_at_its_final_milestone_completes_the_container_journey(): void
    {
        $user = $this->portalUser();
        $shipment = $this->exportShipmentFor($user->companies()->first(), 'BL-EXP-DONE');
        $shipment->update(['current_milestone' => ExportMilestone::FinalChecking]);
        $container = $shipment->containers()->firstOrFail();

        $progress = app(ShipmentTimeline::class)
            ->forContainers($shipment->refresh(), [$container])[$container->getKey()];

        $this->assertNull($progress->current());
        $this->assertSame(6, $progress->reachedCount());
        $this->assertSame(6, $progress->totalSteps());
        $this->assertTrue($progress->isComplete());
        $this->assertSame(ContainerStatus::Completed, $progress->status);

        $this->actingAs($user);

        Livewire::test(ExportShipmentDetail::class, ['exportShipment' => $shipment->getKey()])
            ->assertOk()
            ->assertSee('Final checking shipment details');
    }

    public function test_a_shipment_at_its_first_milestone_shows_containers_as_not_started(): void
    {
        $user = $this->portalUser();
        $shipment = $this->exportShipmentFor($user->companies()->first(), 'BL-EXP-NEW');
        $container = $shipment->containers()->firstOrFail();

        $progress = app(ShipmentTimeline::class)
            ->forContainers($shipment->refresh(), [$container])[$container->getKey()];

        $this->assertSame(0, $progress->reachedCount());
        $this->assertSame(ContainerStatus::Pending, $progress->status);
        $this->assertTrue(collect($progress->steps)->every(
            fn (ShipmentTimelineEntry $step) => $step->isPending,
        ));

        $this->actingAs($user);

        Livewire::test(ExportShipmentDetail::class, ['exportShipment' => $shipment->getKey()])
            ->assertOk()
            ->assertSee('Not started')
            ->assertSee('journey starts at Pick up empty container at depot.');
    }

    public function test_only_web_urls_become_tracking_links(): void
    {
        $user = $this->portalUser();
        $company = $user->companies()->firstOrFail();
        $good = $this->exportShipmentFor($company, 'BL-EXP-TRACK');
        $bad = $this->exportShipmentFor($company, 'BL-EXP-TRACK-XSS');

        $good->update(['current_milestone' => ExportMilestone::OnTheWayToFactory]);
        $bad->update(['current_milestone' => ExportMilestone::OnTheWayToFactory]);

        $goodContainer = $good->containers()->firstOrFail();
        $badContainer = $bad->containers()->firstOrFail();
        $goodContainer->update(['tracking_position_url' => 'https://tracking.example.test/x']);
        $badContainer->update(['tracking_position_url' => 'javascript:alert(1)']);

        $timeline = app(ShipmentTimeline::class);
        $goodProgress = $timeline->forContainers($good->refresh(), [$goodContainer->refresh()])[$goodContainer->getKey()];
        $badProgress = $timeline->forContainers($bad->refresh(), [$badContainer->refresh()])[$badContainer->getKey()];

        $this->assertSame('https://tracking.example.test/x', $goodProgress->trackingUrl);
        $goodField = collect($goodProgress->steps[1]->fields)->firstWhere('label', 'Tracking position (url)');
        $this->assertSame('Open tracking link', $goodField['value']);
        $this->assertSame('https://tracking.example.test/x', $goodField['href']);

        $this->assertNull($badProgress->trackingUrl);
        $badField = collect($badProgress->steps[1]->fields)->firstWhere('label', 'Tracking position (url)');
        $this->assertSame('javascript:alert(1)', $badField['value']);
        $this->assertArrayNotHasKey('href', $badField);

        $this->actingAs($user);

        Livewire::test(ExportShipmentDetail::class, ['exportShipment' => $good->getKey()])
            ->assertOk()
            ->assertSee('Track live')
            ->assertSee('https://tracking.example.test/x', false);

        Livewire::test(ExportShipmentDetail::class, ['exportShipment' => $bad->getKey()])
            ->assertOk()
            ->assertDontSee('Track live')
            ->assertDontSee('href="javascript:alert(1)"', false);
    }

    public function test_a_cancelled_container_stays_cancelled_whatever_the_milestone(): void
    {
        // Only import containers carry an editable status, so a cancellation
        // can only come from an import container.
        $user = $this->portalUser();
        $shipment = $this->importShipmentFor($user->companies()->first(), 'BL-IMP-CXL');
        $shipment->update(['current_milestone' => ImportMilestone::GateOutCy]);
        $container = $shipment->containers()->firstOrFail();
        $container->update(['status' => ContainerStatus::Cancelled]);

        $progress = app(ShipmentTimeline::class)
            ->forContainers($shipment->refresh(), [$container->refresh()])[$container->getKey()];

        $this->assertSame(ContainerStatus::Cancelled, $progress->status);

        $this->actingAs($user);

        Livewire::test(ImportShipmentDetail::class, ['importShipment' => $shipment->getKey()])
            ->assertOk()
            ->assertSee('Cancelled')
            ->assertSee('This container was cancelled.');
    }

    public function test_container_row_chips_appear_only_once_their_milestone_unlocks(): void
    {
        $user = $this->portalUser();
        $company = $user->companies()->firstOrFail();

        $export = $this->exportShipmentFor($company, 'BL-EXP-CHIPS');
        $export->containers()->firstOrFail()->update([
            'seal_number' => 'SEAL-CHIPS',
            'vgm_value' => '1234.500',
        ]);

        $import = $this->importShipmentFor($company, 'BL-IMP-CHIPS');
        $import->containers()->firstOrFail()->update(['gross_weight' => '1020.500']);

        $this->actingAs($user);

        // At the first milestone none of these fields are unlocked yet, so a
        // stray stored value must not leak onto the row.
        Livewire::test(ExportShipmentDetail::class, ['exportShipment' => $export->getKey()])
            ->assertDontSee('VGM 1234.500 kg');
        Livewire::test(ImportShipmentDetail::class, ['importShipment' => $import->getKey()])
            ->assertDontSee('Gross weight 1020.500 kg');

        $export->update(['current_milestone' => ExportMilestone::GateInCy]);
        $import->update(['current_milestone' => ImportMilestone::GateOutCy]);

        // The seal lives in its own column, never as a row chip.
        Livewire::test(ExportShipmentDetail::class, ['exportShipment' => $export->getKey()])
            ->assertSee('VGM 1234.500 kg')
            ->assertDontSee('Seal SEAL-CHIPS');
        Livewire::test(ImportShipmentDetail::class, ['importShipment' => $import->getKey()])
            ->assertSee('Gross weight 1020.500 kg');
    }

    public function test_container_summary_tiles_follow_the_admin_milestone_lock(): void
    {
        $user = $this->portalUser();
        $shipment = $this->importShipmentFor($user->companies()->first(), 'BL-IMP-TILES');
        $container = $shipment->containers()->firstOrFail();
        $container->update([
            'size' => '40',
            'gross_weight' => '1020.500',
            'description_of_goods' => 'Consumer electronics',
        ]);

        $timeline = app(ShipmentTimeline::class);

        // The cargo fields are still locked in the admin before Response
        // billing, so filled values must not leak into the summary tiles.
        $progress = $timeline->forContainers($shipment->refresh(), [$container->refresh()])[$container->getKey()];
        $this->assertSame([], $progress->summary);

        $shipment->update(['current_milestone' => ImportMilestone::ResponseBilling]);

        $tiles = collect(
            $timeline->forContainers($shipment->refresh(), [$container->refresh()])[$container->getKey()]->summary,
        )->pluck('value', 'label');

        $this->assertSame('40 ft', $tiles['Container Size']);
        $this->assertSame('1020.500', $tiles['Gross weight (kg)']);
        $this->assertSame('Consumer electronics', $tiles['Description of goods']);
    }

    public function test_container_rows_show_the_latest_step_with_its_logged_time(): void
    {
        $user = $this->portalUser();
        $shipment = $this->exportShipmentFor($user->companies()->first(), 'BL-EXP-LATEST');

        // Advancing writes a milestone_changed log per step, which is where
        // the row's latest-event timestamp comes from.
        $shipment->advanceMilestone();
        $shipment->advanceMilestone();
        $shipment->refresh();

        $logged = $shipment->activityLogs()
            ->where('event', 'milestone_changed')
            ->orderByDesc('occurred_at')
            ->firstOrFail();

        $this->actingAs($user);

        Livewire::test(ExportShipmentDetail::class, ['exportShipment' => $shipment->getKey()])
            ->assertOk()
            ->assertSee('Pick up empty container at depot')
            ->assertSee($logged->occurred_at->format('d M Y H:i'));
    }

    public function test_the_sailing_card_shows_reached_values_with_admin_labels(): void
    {
        $user = $this->portalUser();
        $shipment = $this->exportShipmentFor($user->companies()->first(), 'BL-EXP-SAIL');
        $shipment->update([
            'current_milestone' => ExportMilestone::PickupEmptyContainer,
            'shipping_line' => 'Test Line',
            'vessel_name' => 'MV Test',
            'voyage_number' => 'V-001',
            'port_of_loading' => 'Tanjung Priok',
            'port_of_discharge' => 'Singapore',
            // Locked until Gate in CY: set anyway to prove the card only
            // surfaces reached milestones.
            'departure_date' => '2026-09-25',
            'eta_at' => '2026-10-01 08:00:00',
        ]);

        $this->actingAs($user);

        Livewire::test(ExportShipmentDetail::class, ['exportShipment' => $shipment->getKey()])
            ->assertOk()
            ->assertSee('Port of loading')
            ->assertSee('Tanjung Priok')
            ->assertSee('Port of discharge')
            ->assertSee('Singapore')
            ->assertSee('Vessel name')
            ->assertSee('MV Test')
            ->assertSee('Voyage number')
            ->assertSee('V-001')
            ->assertSee('Shipping line')
            ->assertSee('Test Line')
            ->assertDontSee('Departure date')
            ->assertDontSee('Arrival time / ETA');
    }

    public function test_the_sailing_card_prefers_actual_arrival_over_eta(): void
    {
        $user = $this->portalUser();
        $shipment = $this->importShipmentFor($user->companies()->first(), 'BL-IMP-ARRIVED');
        $shipment->update([
            'current_milestone' => ImportMilestone::EmptyReturned,
            'port_of_discharge' => 'Singapore',
            'eta_at' => '2026-10-01 08:00:00',
            'actual_arrival_at' => '2026-10-02 09:00:00',
        ]);

        $this->actingAs($user);

        // The card's route side shows "Actual arrival · ..."; the ETA label
        // with the middot separator only renders when no actual exists. The
        // Tracking progress list still shows the ETA field on its own row.
        Livewire::test(ImportShipmentDetail::class, ['importShipment' => $shipment->getKey()])
            ->assertOk()
            ->assertSee('Actual arrival · 02 Oct 2026 09:00')
            ->assertDontSee('Arrival time / ETA ·');
    }

    public function test_the_sailing_card_is_absent_until_a_sailing_value_is_reached(): void
    {
        $user = $this->portalUser();
        $shipment = $this->exportShipmentFor($user->companies()->first(), 'BL-EXP-NOSAIL');
        // Even a stored ETA must not surface: its milestone is not reached.
        $shipment->update(['eta_at' => '2026-10-01 08:00:00']);

        $this->actingAs($user);

        Livewire::test(ExportShipmentDetail::class, ['exportShipment' => $shipment->getKey()])
            ->assertOk()
            ->assertDontSee('Port of loading');
    }

    public function test_the_shipment_summary_shows_status_completion_and_document_date(): void
    {
        $user = $this->portalUser();
        $company = $user->companies()->firstOrFail();
        $done = $this->exportShipmentFor($company, 'BL-EXP-SUMMARY', ShipmentStatus::Completed);
        $done->update([
            'document_received_date' => '2026-09-01',
            'completed_at' => '2026-09-20 10:00:00',
        ]);
        $running = $this->exportShipmentFor($company, 'BL-EXP-RUNNING');

        $this->actingAs($user);

        Livewire::test(ExportShipmentDetail::class, ['exportShipment' => $done->getKey()])
            ->assertOk()
            ->assertSee('Status')
            ->assertSee('Completed')
            ->assertSee('Document received date')
            ->assertSee('01 Sep 2026')
            ->assertSee('Completed at')
            ->assertSee('20 Sep 2026');

        // No completed_at -> no tile; the running pill reads In Progress.
        Livewire::test(ExportShipmentDetail::class, ['exportShipment' => $running->getKey()])
            ->assertOk()
            ->assertSee('In Progress')
            ->assertDontSee('Completed at');
    }

    public function test_the_dashboard_lists_pod_and_vessel_arrival(): void
    {
        $user = $this->portalUser();
        $company = $user->companies()->firstOrFail();

        $arrived = $this->importShipmentFor($company, 'BL-IMP-POD-ACTUAL');
        $arrived->update([
            'current_milestone' => ImportMilestone::EmptyReturned,
            'port_of_discharge' => 'Singapore',
            'eta_at' => '2026-10-01 08:00:00',
            'actual_arrival_at' => '2026-10-02 09:00:00',
        ]);

        $sailing = $this->exportShipmentFor($company, 'BL-EXP-POD-ETA');
        $sailing->update([
            'current_milestone' => ExportMilestone::GateInCy,
            'port_of_discharge' => 'Hong Kong',
            'eta_at' => '2026-10-05 14:30:00',
        ]);

        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertSee('POD / Vessel arrival')
            ->assertSee('Singapore')
            // Actual arrival wins over the ETA on the same row.
            ->assertSee('02 Oct 2026 09:00')
            ->assertDontSee('ETA 01 Oct 2026 08:00')
            ->assertSee('Hong Kong')
            ->assertSee('ETA 05 Oct 2026 14:30')
            ->assertSee('Document received date')
            ->assertDontSee('Latest place');
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

        $this->assertTrue($shipment->confirmation_checklist);
        $this->assertSame($user->getKey(), $shipment->confirmed_by);

        $this->assertDatabaseHas('activity_logs', [
            'import_shipment_id' => $shipment->getKey(),
            'event' => 'draft_pib_confirmed',
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
            ->assertSee('This draft PIB is confirmed')
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

        $this->assertTrue($shipment->refresh()->confirmation_checklist);
        $this->assertDatabaseMissing('activity_logs', [
            'import_shipment_id' => $shipment->getKey(),
            'event' => 'draft_pib_revision_requested',
        ]);
    }

    public function test_a_revision_request_is_stored_as_a_note_the_customer_can_see(): void
    {
        $user = $this->portalUser();
        $shipment = $this->importShipmentFor($user->companies()->first(), 'BL-IMP-REVNOTE');

        $this->actingAs($user);

        Livewire::test(ImportShipmentDetail::class, ['importShipment' => $shipment->getKey()])
            ->set('revisionNotes', 'Please change the HS code.')
            ->call('requestRevision')
            ->assertOk()
            ->assertSee('Please change the HS code.');

        $this->assertDatabaseHas('notes', [
            'noteable_type' => ImportShipment::class,
            'noteable_id' => $shipment->getKey(),
            'author_id' => $user->getKey(),
            'body' => 'Please change the HS code.',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'import_shipment_id' => $shipment->getKey(),
            'event' => 'draft_pib_revision_requested',
        ]);
    }

    public function test_notes_from_other_authors_stay_internal(): void
    {
        $user = $this->portalUser();
        $colleague = $this->portalUser('colleague@example.com', 'Colleague Co');
        $shipment = $this->importShipmentFor($user->companies()->first(), 'BL-IMP-INTERNAL');

        $shipment->notes()->create([
            'body' => 'Internal office remark',
            'author_id' => $colleague->getKey(),
        ]);

        $this->actingAs($user);

        Livewire::test(ImportShipmentDetail::class, ['importShipment' => $shipment->getKey()])
            ->assertOk()
            ->assertDontSee('Internal office remark');
    }

    public function test_confirming_an_already_confirmed_draft_changes_nothing(): void
    {
        $user = User::query()->where('email', 'sari@java-retail.test')->firstOrFail();
        $shipment = $this->confirmedImportShipment();

        $this->actingAs($user);

        $before = ActivityLog::query()->count();

        Livewire::test(ImportShipmentDetail::class, ['importShipment' => $shipment->getKey()])
            ->call('confirm')
            ->assertOk()
            ->assertSee('already confirmed');

        $this->assertSame($before, ActivityLog::query()->count());
        $this->assertTrue($shipment->refresh()->confirmation_checklist);
    }

    private function confirmedImportShipment(): ImportShipment
    {
        return ImportShipment::query()
            ->where('confirmation_checklist', true)
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

    private function exportShipmentFor(Company $company, string $reference, ShipmentStatus $status = ShipmentStatus::InProgress): ExportShipment
    {
        $shipment = ExportShipment::query()->create([
            'bl_number' => $reference,
            'company_id' => $company->getKey(),
            'company_name_snapshot' => $company->name,
            'current_milestone' => ExportMilestone::DocumentReceived,
            'status' => $status,
        ]);

        $shipment->containers()->create([
            'container_number' => 'PORTAL'.random_int(100000, 999999),
        ]);

        return $shipment->refresh();
    }

    private function importShipmentFor(Company $company, string $reference, ShipmentStatus $status = ShipmentStatus::InProgress): ImportShipment
    {
        $shipment = ImportShipment::query()->create([
            'bl_number' => $reference,
            'company_id' => $company->getKey(),
            'company_name_snapshot' => $company->name,
            'current_milestone' => ImportMilestone::DocumentReceived,
            'status' => $status,
            'confirmation_checklist' => false,
        ]);

        $shipment->containers()->create([
            'container_number' => 'PORTAL'.random_int(100000, 999999),
        ]);

        return $shipment->refresh();
    }
}
