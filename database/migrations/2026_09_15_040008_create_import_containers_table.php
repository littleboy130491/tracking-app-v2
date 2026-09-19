<?php

/**
 * File: database/migrations/2026_09_15_040008_create_import_containers_table.php
 * Responsibility: Creates the `import_containers` table (a container in an import shipment).
 * What it does:
 * - Each row is a shipment-scoped container holding import fields only:
 *   identity, driver, gate-out, weights, inspection, factory loading and
 *   depot return data.
 * How to use: App\Models\ImportContainer belongsTo ImportShipment; hasMany Attachment.
 * How to extend: Add new import container fields as nullable columns and expose
 *   them in the import container form sections.
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
        Schema::create('import_containers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_shipment_id')->constrained()->cascadeOnDelete();
            $table->string('container_number', 30);
            $table->string('size', 20)->nullable();
            $table->string('type', 30)->nullable();
            $table->string('seal_number', 100)->nullable();

            $table->string('driver_name')->nullable();
            // "No. License": meaning is unconfirmed (migration_plan §14), stored as text.
            $table->string('license_number', 100)->nullable();
            $table->string('driver_license_number', 100)->nullable();

            $table->timestamp('gate_out_cy_at')->nullable();
            $table->decimal('gross_weight', 15, 3)->nullable();
            $table->string('gross_weight_unit', 20)->nullable();
            $table->decimal('cbm', 15, 3)->nullable();

            $table->string('inspection_status', 30)->default('not_started');
            $table->timestamp('inspected_at')->nullable();
            $table->text('inspection_notes')->nullable();

            $table->timestamp('factory_arrived_at')->nullable();
            $table->string('factory_loading_status', 30)->default('not_started');
            $table->timestamp('factory_loading_started_at')->nullable();
            $table->timestamp('factory_loading_finished_at')->nullable();

            $table->string('return_depot_name')->nullable();
            $table->timestamp('empty_returned_at')->nullable();

            $table->string('status', 30)->default('pending');
            $table->string('latest_event', 40)->nullable();
            $table->timestamp('latest_event_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['import_shipment_id', 'container_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_containers');
    }
};
