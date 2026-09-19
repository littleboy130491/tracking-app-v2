<?php

/**
 * File: tests/Feature/Admin/NotesPanelTest.php
 * Responsibility: Verifies the notes panel rules and audit trail.
 * What it does:
 * - Any internal user adds notes and reads everyone's; only the author may
 *   edit or delete theirs; operators cannot reach unassigned shipments.
 * - note_created/updated/deleted land in the activity log with linkage.
 * How to use: `php artisan test --filter=NotesPanelTest`.
 * How to extend: add a case per new noteable type or rule.
 */

namespace Tests\Feature\Admin;

use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\ExportContainers\ExportContainerResource;
use App\Filament\Resources\ExportShipments\ExportShipmentResource;
use App\Filament\Resources\Users\UserResource;
use App\Livewire\NotesPanel;
use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\ExportContainer;
use App\Models\ExportShipment;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotesPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $this->operator = User::query()->where('email', 'operator@example.com')->firstOrFail();
    }

    public function test_a_note_is_created_with_author_and_logged(): void
    {
        $shipment = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();

        $this->actingAs($this->admin);

        Livewire::test(NotesPanel::class, ['record' => $shipment])
            ->set('body', 'Booking confirmed with the agent.')
            ->call('addNote')
            ->assertHasNoErrors()
            ->assertSee('Booking confirmed with the agent.')
            ->assertSee('Admin');

        $note = Note::query()->firstOrFail();
        $this->assertSame($this->admin->getKey(), $note->author_id);
        $this->assertTrue($note->noteable->is($shipment));

        $log = ActivityLog::query()->where('event', 'note_created')->firstOrFail();
        $this->assertSame($shipment->getKey(), $log->export_shipment_id);
        $this->assertSame($this->admin->getKey(), $log->actor_id);
        $this->assertSame(['body' => 'Booking confirmed with the agent.'], $log->new_values);
    }

    public function test_everyone_reads_all_notes_but_only_the_author_edits(): void
    {
        $shipment = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();

        $adminNote = $shipment->notes()->create(['body' => 'Admin note', 'author_id' => $this->admin->getKey()]);

        $this->actingAs($this->operator);

        // The operator sees the admin's note.
        Livewire::test(NotesPanel::class, ['record' => $shipment])
            ->assertSee('Admin note')
            ->call('startEditing', $adminNote->getKey())
            ->assertStatus(403);
    }

    public function test_the_author_can_edit_and_delete_their_note(): void
    {
        $shipment = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();

        $this->actingAs($this->admin);

        $component = Livewire::test(NotesPanel::class, ['record' => $shipment])
            ->set('body', 'First version')
            ->call('addNote');

        $note = Note::query()->firstOrFail();

        $component
            ->call('startEditing', $note->getKey())
            ->set('editingBody', 'Second version')
            ->call('saveEdit')
            ->assertSee('Second version');

        $this->assertSame('Second version', $note->refresh()->body);

        $updateLog = ActivityLog::query()->where('event', 'note_updated')->firstOrFail();
        $this->assertSame(['body' => 'First version'], $updateLog->old_values);
        $this->assertSame(['body' => 'Second version'], $updateLog->new_values);

        $component->call('deleteNote', $note->getKey());

        $this->assertNull(Note::query()->find($note->getKey()));

        $deleteLog = ActivityLog::query()->where('event', 'note_deleted')->firstOrFail();
        $this->assertSame(['body' => 'Second version'], $deleteLog->old_values);
    }

    public function test_notes_work_on_containers_companies_and_users(): void
    {
        $this->actingAs($this->admin);

        $container = ExportContainer::query()->firstOrFail();
        $company = Company::query()->firstOrFail();
        $user = User::query()->where('email', 'customer@example.com')->firstOrFail();

        foreach ([[$container, 'Container note'], [$company, 'Company note'], [$user, 'User note']] as [$target, $body]) {
            Livewire::test(NotesPanel::class, ['record' => $target])
                ->set('body', $body)
                ->call('addNote')
                ->assertHasNoErrors();
        }

        $this->assertSame(1, $container->notes()->count());
        $this->assertSame(1, $company->notes()->count());
        $this->assertSame(1, $user->notes()->count());

        // Container notes carry the shipment link; company/user notes carry none.
        $containerLog = ActivityLog::query()
            ->where('event', 'note_created')
            ->where('export_container_id', $container->getKey())
            ->firstOrFail();
        $this->assertSame($container->export_shipment_id, $containerLog->export_shipment_id);

        $companyLog = ActivityLog::query()
            ->where('event', 'note_created')
            ->whereNull('export_shipment_id')
            ->whereNull('import_shipment_id')
            ->firstOrFail();
        $this->assertNull($companyLog->export_container_id);
    }

    public function test_operator_cannot_reach_notes_on_an_unassigned_shipment(): void
    {
        $hidden = ExportShipment::query()
            ->whereHas('company', fn ($query) => $query->whereNotIn('code', ['NUS', 'SNI']))
            ->firstOrFail();

        $this->actingAs($this->operator);

        $this->expectException(ModelNotFoundException::class);

        Livewire::test(NotesPanel::class, ['record' => $hidden]);
    }

    public function test_the_panel_is_embedded_on_the_edit_pages(): void
    {
        $this->actingAs($this->admin);

        $shipment = ExportShipment::query()->where('bl_number', 'BL-EXP-0001')->firstOrFail();
        $container = $shipment->containers()->firstOrFail();
        $company = $shipment->company;
        $user = User::query()->where('email', 'customer@example.com')->firstOrFail();

        $pages = [
            ExportShipmentResource::getUrl('edit', ['record' => $shipment]),
            ExportContainerResource::getUrl('edit', ['record' => $container]),
            CompanyResource::getUrl('edit', ['record' => $company]),
            UserResource::getUrl('edit', ['record' => $user]),
        ];

        foreach ($pages as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('Add a note for your team');
        }
    }

    public function test_unknown_target_types_are_rejected(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(NotesPanel::class, ['type' => ActivityLog::class, 'id' => 1])
            ->assertStatus(404);
    }

    public function test_deleting_someone_elses_note_is_forbidden(): void
    {
        $shipment = ExportShipment::query()->firstOrFail();
        $note = $shipment->notes()->create(['body' => 'Admin only', 'author_id' => $this->admin->getKey()]);

        $this->actingAs($this->operator);

        Livewire::test(NotesPanel::class, ['record' => $shipment])
            ->call('deleteNote', $note->getKey())
            ->assertStatus(403);

        $this->assertNotNull($note->fresh());
    }
}
