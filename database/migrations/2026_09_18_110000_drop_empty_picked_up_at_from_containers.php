<?php

/**
 * File: database/migrations/2026_09_18_110000_drop_empty_picked_up_at_from_containers.php
 * Responsibility: Drops the unused empty-container pickup timestamp.
 * What it does:
 * - Removes empty_picked_up_at from containers; the pickup step now records
 *   only the depot (on the B/L) and the journey no longer emits that event.
 * How to use: `php artisan migrate`.
 * How to extend: reinstate the column and the ShipmentTimeline row if pickup
 *   times are needed again.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('containers', function (Blueprint $table) {
            $table->dropColumn('empty_picked_up_at');
        });
    }

    public function down(): void
    {
        Schema::table('containers', function (Blueprint $table) {
            $table->timestamp('empty_picked_up_at')->nullable()->after('seal_number');
        });
    }
};
