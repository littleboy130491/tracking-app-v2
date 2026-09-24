<?php

/**
 * File: database/migrations/2026_09_24_141100_add_seal_number_to_import_containers_table.php
 * Responsibility: Adds the seal_number column to import_containers.
 * What it does:
 * - IMPORT.md Step 11 lists Seal number per container; the column sits after
 *   size and mirrors the export container seal.
 * How to use: `php artisan migrate` (or migrate:fresh --seed).
 * How to extend: nothing — the import forms and timeline read it directly.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_containers', function (Blueprint $table) {
            $table->string('seal_number', 100)->nullable()->after('size');
        });
    }

    public function down(): void
    {
        Schema::table('import_containers', function (Blueprint $table) {
            $table->dropColumn('seal_number');
        });
    }
};
