<?php

/**
 * File: app/Models/Note.php
 * Responsibility: One free-text note attached to a record.
 * What it does:
 * - Stores the body, its author, and the polymorphic target (shipment,
 *   container, company or user).
 * - Author-only editing is enforced by NotePolicy via isEditableBy().
 * How to use: `$shipment->notes()->create([...])`, `$note->author`.
 * How to extend: add fields here and to the notes panel component.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['body', 'author_id'])]
class Note extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function noteable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Only the author may edit or delete their own note.
     */
    public function isEditableBy(?User $user): bool
    {
        return $user !== null && $this->author_id === $user->getKey();
    }
}
