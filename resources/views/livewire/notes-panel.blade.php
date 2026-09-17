{{-- File: resources/views/livewire/notes-panel.blade.php
     Responsibility: Add form plus newest-first notes list for one record.
     What it does: shows author and time per note; edit/delete only on your own.
     How to use: rendered by App\Livewire\NotesPanel.
     How to extend: keep markup in sync with the NotesPanel properties. --}}
<div class="space-y-4">
    @if ($editingId === null)
        <div class="space-y-2">
            <x-filament::input.wrapper>
                <x-filament::input
                    type="textarea"
                    rows="3"
                    wire:model="body"
                    placeholder="Add a note for your team…"
                />
            </x-filament::input.wrapper>

            @error('body')
                <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
            @enderror

            <x-filament::button size="sm" wire:click="addNote">
                Add note
            </x-filament::button>
        </div>
    @endif

    <div class="space-y-3">
        @forelse ($notes as $note)
            <div
                wire:key="note-{{ $note->id }}"
                class="rounded-lg border border-gray-200 p-3 dark:border-white/10"
            >
                @if ($editingId === $note->id)
                    <div class="space-y-2">
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="textarea"
                                rows="3"
                                wire:model="editingBody"
                            />
                        </x-filament::input.wrapper>

                        @error('editingBody')
                            <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                        @enderror

                        <div class="flex gap-2">
                            <x-filament::button size="sm" wire:click="saveEdit">
                                Save
                            </x-filament::button>
                            <x-filament::button size="sm" color="gray" wire:click="cancelEditing">
                                Cancel
                            </x-filament::button>
                        </div>
                    </div>
                @else
                    <div class="whitespace-pre-line text-sm">{{ $note->body }}</div>

                    <div class="mt-2 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ $note->author?->name ?? 'Deleted user' }}</span>
                        <span>·</span>
                        <span title="{{ $note->created_at->toDayDateTimeString() }}">
                            {{ $note->created_at->diffForHumans() }}
                        </span>
                        @if ($note->updated_at->gt($note->created_at))
                            <span>·</span>
                            <span>edited</span>
                        @endif

                        @can('update', $note)
                            <span class="ms-auto flex gap-2">
                                <x-filament::link size="sm" wire:click="startEditing({{ $note->id }})">
                                    Edit
                                </x-filament::link>
                                <x-filament::link
                                    size="sm"
                                    color="danger"
                                    wire:click="deleteNote({{ $note->id }})"
                                    wire:confirm="Delete this note?"
                                >
                                    Delete
                                </x-filament::link>
                            </span>
                        @endcan
                    </div>
                @endif
            </div>
        @empty
            <p class="text-sm text-gray-500 dark:text-gray-400">No notes yet.</p>
        @endforelse
    </div>
</div>
