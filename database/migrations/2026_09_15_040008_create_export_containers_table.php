<?php

/**
 * File: database/migrations/2026_09_15_040008_create_export_containers_table.php
 * Responsibility: Creates the `export_containers` table (a container in an export shipment).
 * What it does:
 * - Each row is a shipment-scoped container holding export fields only:
 *   identity, driver, tracking, stuffing, gate-in and VGM data.
 * How to use: App\Models\ExportContainer belongsTo ExportShipment; hasMany Attachment.
 * How to extend: Add new export container fields as nullable columns and expose
 *   them in the export container form sections.
 *
 * Note: container_number is unique per shipment, not globally, because the same
 * physical container can be reused on another shipment.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('export_containers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('export_shipment_id')->constrained()->cascadeOnDelete();
            $table->string('container_number', 30);
            $table->string('size', 20)->nullable();
            $table->string('type', 30)->nullable();
            $table->string('seal_number', 100)->nullable();

            $table->string('driver_name')->nullable();
            // "No. License": meaning is unconfirmed (migration_plan §14), stored as text.
            $table->string('license_number', 100)->nullable();
            $table->string('driver_license_number', 100)->nullable();
            // Free-text driver position reported while on the way to the factory.
            $table->string('tracking_position')->nullable();
            $table->string('tracking_position_url', 500)->nullable();

            $table->string('stuffing_status', 30)->default('not_started');

            $table->string('port_of_loading')->nullable();
            $table->timestamp('gate_in_cy_at')->nullable();
            // VGM is recorded in kilograms per the export spec; no unit column.
            $table->decimal('vgm_value', 15, 3)->nullable();
            $table->boolean('final_checked')->default(false);
            $table->timestamp('final_checked_at')->nullable();

            $table->string('status', 30)->default('pending');
            $table->string('latest_event', 40)->nullable();
            $table->timestamp('latest_event_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['export_shipment_id', 'container_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('export_containers');
    }
};
