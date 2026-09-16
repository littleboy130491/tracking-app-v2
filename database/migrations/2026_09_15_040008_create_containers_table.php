<?php

/**
 * File: database/migrations/2026_09_15_040008_create_containers_table.php
 * Responsibility: Creates the `containers` table (a container within a shipment).
 * What it does:
 * - Each row is a shipment-scoped container, not a reusable asset master.
 * - Tracks stuffing, inspection, factory loading, weights and depot returns.
 * How to use: App\Models\Container belongsTo BillOfLading; hasMany ContainerLocationUpdate.
 * How to extend: Add new container fields as nullable columns; user-defined
 *   fields are managed in the Filament form sections.
 *
 * Note: container_number is unique per B/L, not globally, because the same
 * physical container can be reused on another shipment.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('containers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_of_lading_id')->constrained()->cascadeOnDelete();
            $table->string('container_number', 30);
            $table->string('size', 20)->nullable();
            $table->string('type', 30)->nullable();
            $table->string('seal_number', 100)->nullable();

            $table->string('pickup_depot_name')->nullable();
            $table->timestamp('empty_picked_up_at')->nullable();

            $table->date('stuffing_date')->nullable();
            $table->text('stuffing_destination')->nullable();
            $table->string('stuffing_status', 30)->default('not_started');
            $table->timestamp('stuffing_started_at')->nullable();
            $table->timestamp('stuffing_finished_at')->nullable();
            $table->string('driver_name')->nullable();
            // "No. License": meaning is unconfirmed (migration_plan §14), stored as text.
            $table->string('license_number', 100)->nullable();
            // Free-text driver position reported while on the way to the factory.
            $table->string('tracking_position')->nullable();

            $table->decimal('gross_weight', 15, 3)->nullable();
            $table->string('gross_weight_unit', 20)->nullable();
            $table->decimal('cbm', 15, 3)->nullable();
            // VGM is recorded in kilograms per the export spec; no unit column.
            $table->decimal('vgm_value', 15, 3)->nullable();

            $table->string('gate_in_port_name')->nullable();
            $table->timestamp('gate_in_cy_at')->nullable();
            $table->timestamp('gate_out_cy_at')->nullable();

            $table->string('inspection_status', 30)->default('not_started');
            $table->timestamp('inspected_at')->nullable();
            $table->text('inspection_notes')->nullable();

            $table->timestamp('factory_arrived_at')->nullable();
            $table->string('factory_loading_status', 30)->default('not_started');
            $table->timestamp('factory_loading_started_at')->nullable();
            $table->timestamp('factory_loading_finished_at')->nullable();
            $table->boolean('final_checked')->default(false);
            $table->timestamp('final_checked_at')->nullable();

            $table->string('return_depot_name')->nullable();
            $table->timestamp('empty_returned_at')->nullable();

            $table->string('status', 30)->default('pending');
            $table->timestamp('completed_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['bill_of_lading_id', 'container_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('containers');
    }
};
