<?php

/**
 * File: database/migrations/2026_09_17_170000_add_export_spec_columns_to_shipment_tables.php
 * Responsibility: Adds the EXPORT.md spec columns and latest-event stamps.
 * What it does:
 * - B/L: document_received_date/by and the denormalized latest_event pair.
 * - Container: driver_license_number, tracking_position_url and the same
 *   latest_event pair.
 * How to use: `php artisan migrate`.
 * How to extend: the logger keeps latest_event current; no backfill needed.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bill_of_ladings', function (Blueprint $table) {
            $table->date('document_received_date')->nullable()->after('company_name_snapshot');
            $table->foreignId('document_received_by')->nullable()->constrained('users')->nullOnDelete()->after('document_received_date');
            $table->string('latest_event', 40)->nullable()->after('current_milestone');
            $table->timestamp('latest_event_at')->nullable()->after('latest_event');
        });

        Schema::table('containers', function (Blueprint $table) {
            $table->string('driver_license_number', 100)->nullable()->after('license_number');
            $table->string('tracking_position_url', 500)->nullable()->after('tracking_position');
            $table->string('latest_event', 40)->nullable()->after('status');
            $table->timestamp('latest_event_at')->nullable()->after('latest_event');
        });
    }

    public function down(): void
    {
        Schema::table('bill_of_ladings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('document_received_by');
            $table->dropColumn('document_received_date');
        });

        Schema::table('containers', function (Blueprint $table) {
            $table->dropColumn(['driver_license_number', 'tracking_position_url']);
        });

        Schema::table('bill_of_ladings', function (Blueprint $table) {
            $table->dropColumn(['latest_event', 'latest_event_at']);
        });

        Schema::table('containers', function (Blueprint $table) {
            $table->dropColumn(['latest_event', 'latest_event_at']);
        });
    }
};
