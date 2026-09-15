<?php

/**
 * File: database/migrations/2026_09_15_040007_create_hs_codes_table.php
 * Responsibility: Creates the `hs_codes` master table and its B/L pivot.
 * What it does:
 * - HS codes are shared master data (one row per code) so the same code can
 *   be reused across shipments; `bill_of_lading_hs_code` links them.
 * How to use: App\Models\HsCode belongsToMany BillOfLading; the B/L form's
 *   multi-select attaches codes and can create new ones inline.
 * How to extend: Add quantity/unit columns on the pivot if per-shipment
 *   volumes are needed.
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
        });

        Schema::create('bill_of_lading_hs_code', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_of_lading_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hs_code_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['bill_of_lading_id', 'hs_code_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_of_lading_hs_code');
        Schema::dropIfExists('hs_codes');
    }
};
