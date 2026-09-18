<?php

/**
 * File: database/migrations/2026_09_18_100000_move_pickup_stuffing_columns_to_bill_of_ladings.php
 * Responsibility: Moves the pickup/stuffing fields from containers to the shipment header.
 * What it does:
 * - Adds pickup_depot_name, stuffing_date and stuffing_destination to bill_of_ladings.
 * - Drops the same three columns from containers.
 * How to use: `php artisan migrate`.
 * How to extend: these are per-shipment now; per-container pickup fields would need a new pair of columns.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bill_of_ladings', function (Blueprint $table) {
            $table->string('pickup_depot_name')->nullable()->after('bl_number');
            $table->date('stuffing_date')->nullable()->after('pickup_depot_name');
            $table->text('stuffing_destination')->nullable()->after('stuffing_date');
        });

        Schema::table('containers', function (Blueprint $table) {
            $table->dropColumn(['pickup_depot_name', 'stuffing_date', 'stuffing_destination']);
        });
    }

    public function down(): void
    {
        Schema::table('bill_of_ladings', function (Blueprint $table) {
            $table->dropColumn(['pickup_depot_name', 'stuffing_date', 'stuffing_destination']);
        });

        Schema::table('containers', function (Blueprint $table) {
            $table->string('pickup_depot_name')->nullable()->after('driver_license_number');
            $table->date('stuffing_date')->nullable()->after('pickup_depot_name');
            $table->text('stuffing_destination')->nullable()->after('stuffing_date');
        });
    }
};
