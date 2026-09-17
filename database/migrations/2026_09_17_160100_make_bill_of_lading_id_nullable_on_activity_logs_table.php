<?php

/**
 * File: database/migrations/2026_09_17_160100_make_bill_of_lading_id_nullable_on_activity_logs_table.php
 * Responsibility: Allows activity logs without a shipment.
 * What it does:
 * - Notes on companies or users have no bill of lading, so the column must
 *   accept null while notes on shipments/containers keep the linkage.
 * How to use: `php artisan migrate`.
 * How to extend: nothing further; the logger sets the column per target.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('bill_of_lading_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('bill_of_lading_id')->nullable(false)->change();
        });
    }
};
