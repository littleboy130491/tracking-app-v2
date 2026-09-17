<?php

/**
 * File: database/migrations/2026_09_17_160000_create_notes_table.php
 * Responsibility: Creates the notes table.
 * What it does:
 * - Free-text notes attached to any record (bill of lading, container,
 *   company, user) through a polymorphic `noteable` relation.
 * - author_id is nullable so deleting a user keeps their notes readable.
 * How to use: `php artisan migrate` (or migrate:fresh --seed).
 * How to extend: add columns here, then to the Note model and notes panel.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->text('body');
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->morphs('noteable');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
