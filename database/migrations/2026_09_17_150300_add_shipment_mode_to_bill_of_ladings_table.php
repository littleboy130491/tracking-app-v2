<?php

/**
 * File: database/migrations/2026_09_17_150300_add_shipment_mode_to_bill_of_ladings_table.php
 * Responsibility: Adds the `shipment_mode` column to bill_of_ladings.
 * What it does:
 * - Nullable string (FCL / LCL / Air); existing rows stay null, new
 *   shipments pick a mode on the Document tab.
 * How to use: `php artisan migrate`.
 * How to extend: Backfill old rows via a seeder if modes become required.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bill_of_ladings', function (Blueprint $table) {
            $table->string('shipment_mode', 20)->nullable()->after('shipment_type');
        });
    }

    public function down(): void
    {
        Schema::table('bill_of_ladings', function (Blueprint $table) {
            $table->dropColumn('shipment_mode');
        });
    }
};
