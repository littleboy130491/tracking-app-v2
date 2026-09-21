<?php

/**
 * File: app/Livewire/NotesPanel.php
 * Responsibility: Add, read, edit and delete notes on one record.
 * What it does:
 * - Any internal user sees every note and adds their own; NotePolicy allows
 *   only the author to edit or delete a note.
 * - Every change is recorded in the activity log (note_created/updated/deleted).
 * How to use: embedded via Filament's LivewireField on edit forms; the target
 *   arrives as the form's record.
 * How to extend: add fields to validation and the notes-panel view.
 */

namespace App\Livewire;

use App\Filament\Resources\ExportContainers\ExportContainerResource;
use App\Filament\Resources\ExportShipments\ExportShipmentResource;
use App\Filament\Resources\ImportContainers\ImportContainerResource;
use App\Filament\Resources\ImportShipments\ImportShipmentResource;
use App\Models\Company;
use App\Models\ExportContainer;
use App\Models\ExportShipment;
use App\Models\ImportContainer;
use App\Models\ImportShipment;
use App\Models\Note;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class NotesPanel extends Component
{
    /** Targets notes may attach to; also the whitelist for mount(). */
    public const TYPES = [
        ExportShipment::class,
        ImportShipment::class,
        ExportContainer::class,
        ImportContainer::class,
        Company::class,
        User::class,
    ];

    #[Locked]
    public string $noteableType = '';

    #[Locked]
    public int $noteableId = 0;

    public string $body = '';

    public ?int $editingId = null;

    public string $editingBody = '';

    public function mount(?Model $record = null, ?string $type = null, ?int $id = null): void
    {
        if ($record !== null) {
            $type ??= $record::class;
            $id ??= $record->getKey();
        }

        if (! in_array($type, self::TYPES, true)) {
            abort(404);
        }

        $this->noteableType = $type;
        $this->noteableId = (int) $id;

        Gate::authorize('view', $this->noteable());
    }

    public function addNote(): void
    {
        $this->validate(['body' => 'required|string|max:2000']);

        $note = $this->noteable()->notes()->create([
            'body' => $this->body,
            'author_id' => auth()->id(),
        ]);

        app(ActivityLogger::class)->recordNote($note, 'note_created', newValues: ['body' => $note->body]);

        $this->reset('body');
    }

    public function startEditing(int $id): void
    {
        $note = $this->note($id);

        Gate::authorize('update', $note);

        $this->editingId = $note->getKey();
        $this->editingBody = $note->body;
    }

    public function cancelEditing(): void
    {
        $this->reset('editingId', 'editingBody');
    }

    public function saveEdit(): void
    {
        $note = $this->note((int) $this->editingId);

        Gate::authorize('update', $note);

        $this->validate(['editingBody' => 'required|string|max:2000']);

        $old = $note->body;
        $note->update(['body' => $this->editingBody]);

        app(ActivityLogger::class)->recordNote(
            $note,
            'note_updated',
            oldValues: ['body' => $old],
            newValues: ['body' => $note->body],
        );

        $this->cancelEditing();
    }

    public function deleteNote(int $id): void
    {
        $note = $this->note($id);

        Gate::authorize('delete', $note);

        $body = $note->body;
        $note->delete();

        app(ActivityLogger::class)->recordNote($note, 'note_deleted', oldValues: ['body' => $body]);
    }

    public function render(): View
    {
        return view('livewire.notes-panel', [
            'notes' => $this->noteable()->notes()->with('author')->latest()->get(),
        ]);
    }

    /**
     * Resolves the target through the same row-level scope as its resource,
     * so operators can never reach notes on unassigned shipments.
     */
    private function noteable(): Model
    {
        return match ($this->noteableType) {
            ExportShipment::class => ExportShipmentResource::getEloquentQuery()->findOrFail($this->noteableId),
            ImportShipment::class => ImportShipmentResource::getEloquentQuery()->findOrFail($this->noteableId),
            ExportContainer::class => ExportContainerResource::getEloquentQuery()->findOrFail($this->noteableId),
            ImportContainer::class => ImportContainerResource::getEloquentQuery()->findOrFail($this->noteableId),
            // Company/User may be trashed; their edit pages stay reachable for
            // restore, so resolve them without the soft-delete scope too.
            default => $this->noteableType::query()
                ->withoutGlobalScopes([SoftDeletingScope::class])
                ->findOrFail($this->noteableId),
        };
    }

    private function note(int $id): Note
    {
        return $this->noteable()->notes()->findOrFail($id);
    }
}
