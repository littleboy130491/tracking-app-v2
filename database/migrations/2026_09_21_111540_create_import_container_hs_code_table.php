<?php

/**
 * File: database/migrations/2026_09_21_111540_create_import_container_hs_code_table.php
 * Responsibility: Creates the `import_container_hs_code` pivot linking import containers to HS codes.
 * What it does:
 * - Container-level HS codes: a container can carry several codes, and each
 *   code can appear on several containers; the model seeds a new container
 *   from its shipment's codes so the operator only overrides exceptions.
 * - Cascade-deletes with both sides, so deleting a container or retiring a
 *   code never leaves orphaned links.
 * How to use: App\Models\ImportContainer belongsToMany HsCode (and the reverse).
 * How to extend: Add quantity/unit columns on the pivot if per-container
 *   volumes are needed.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_container_hs_code', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_container_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hs_code_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['import_container_id', 'hs_code_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_container_hs_code');
    }
};
