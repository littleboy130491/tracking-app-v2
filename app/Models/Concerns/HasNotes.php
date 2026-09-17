<?php

/**
 * File: app/Models/Concerns/HasNotes.php
 * Responsibility: Adds the polymorphic notes relation to a model.
 * What it does:
 * - `$model->notes` returns every note attached to the record.
 * How to use: `use HasNotes;` inside the model class.
 * How to extend: nothing to change here; extend the Note model instead.
 */

namespace App\Models\Concerns;

use App\Models\Note;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasNotes
{
    /**
     * @return MorphMany<Note, $this>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }
}
