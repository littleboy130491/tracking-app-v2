<?php

/**
 * File: database/migrations/2026_09_23_053832_add_confirmed_by_to_import_shipments_table.php
 * Responsibility: Adds the confirmed_by column to import_shipments.
 * What it does:
 * - Records which user set the draft-PIB confirmation checklist (a portal
 *   customer or an office user); null while unconfirmed.
 * How to use: `php artisan migrate` (or migrate:fresh --seed).
 * How to extend: nothing — the model's saving hook maintains the value.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_shipments', function (Blueprint $table) {
            $table->foreignId('confirmed_by')
                ->nullable()
                ->after('confirmation_checklist')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('import_shipments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('confirmed_by');
        });
    }
};
