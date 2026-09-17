<?php

/**
 * File: app/Policies/NotePolicy.php
 * Responsibility: Who may read and change a note.
 * What it does:
 * - Any internal user may read every note and add their own.
 * - Only the author may update or delete a note.
 * How to use: Filament and the notes panel call the standard ability names.
 * How to extend: widen update/delete here if a moderator role appears.
 */

namespace App\Policies;

use App\Models\Note;
use App\Models\User;

class NotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isInternal();
    }

    public function view(User $user, Note $note): bool
    {
        return $user->isInternal();
    }

    public function create(User $user): bool
    {
        return $user->isInternal();
    }

    public function update(User $user, Note $note): bool
    {
        return $note->isEditableBy($user);
    }

    public function delete(User $user, Note $note): bool
    {
        return $note->isEditableBy($user);
    }
}
