<?php

/**
 * File: database/migrations/2026_09_15_040008_create_import_containers_table.php
 * Responsibility: Creates the `import_containers` table (a container in an import shipment).
 * What it does:
 * - Each row is a shipment-scoped container holding import fields only, one
 *   column group per IMPORT.md milestone: identity, cargo (description of
 *   goods and packages), gate-out, tracking (free-text position plus a
 *   validated URL), weights, factory loading and depot return data.
 * How to use: App\Models\ImportContainer belongsTo ImportShipment; belongsToMany
 *   HsCode (import_container_hs_code pivot); hasMany Attachment.
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
            // Response billing — No. container / Tambahan step SPJM — size.
            $table->string('container_number', 30);
            $table->string('size', 20)->nullable();
            // Container cargo — response billing / upload all document: each
            // container carries its own description of goods and packages.
            $table->text('description_of_goods')->nullable();
            $table->string('packages', 255)->nullable();

            // Gate out from inbound terminal — driver name and No. license.
            $table->string('driver_name')->nullable();
            // "No. License": meaning is unconfirmed (migration_plan §14), stored as text.
            $table->string('license_number', 100)->nullable();
            $table->timestamp('gate_out_cy_at')->nullable();

            // Container on the way factory — driver tracking.
            $table->string('tracking_position')->nullable();
            $table->string('tracking_position_url', 500)->nullable();

            // Payment bahandle — gross weight.
            $table->decimal('gross_weight', 15, 3)->nullable();
            $table->string('gross_weight_unit', 20)->nullable();
            // Waiting change status SPJM to SPPB — CBM / measurement.
            $table->decimal('cbm', 15, 3)->nullable();

            // Container arrived in factory — loading date and status.
            $table->timestamp('factory_loading_at')->nullable();
            $table->string('factory_loading_status', 30)->default('on_process');

            // Empty container returned — depot and return date.
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
