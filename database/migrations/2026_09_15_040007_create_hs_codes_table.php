<?php

/**
 * File: database/migrations/2026_09_15_040007_create_hs_codes_table.php
 * Responsibility: Creates the `hs_codes` master table and its shipment pivot.
 * What it does:
 * - HS codes are shared master data (one row per code) so the same code can
 *   be reused across shipments; one pivot links them to import shipments.
 * - Soft-deletable: retiring a code keeps the shipment links intact; admin
 *   may restore, only super_admin may permanently delete.
 * How to use: App\Models\HsCode belongsToMany ImportShipment / ImportContainer.
 * How to extend: Add quantity/unit columns on a pivot if per-shipment volumes
 *   are needed.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hs_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('import_shipment_hs_code', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_shipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hs_code_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['import_shipment_id', 'hs_code_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_shipment_hs_code');
        Schema::dropIfExists('hs_codes');
    }
};
