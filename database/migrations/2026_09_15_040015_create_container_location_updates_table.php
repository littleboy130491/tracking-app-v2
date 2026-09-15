<?php

/**
 * File: database/migrations/2026_09_15_040015_create_container_location_updates_table.php
 * Responsibility: Creates the `container_location_updates` table.
 * What it does:
 * - Append-only history of manually reported container positions.
 * - Records which stage the update belongs to and whether customers may see it.
 * How to use: App\Models\ContainerLocationUpdate belongsTo Container.
 * How to extend: Add a geofence or accuracy column if GPS data is added.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('container_location_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_id')->constrained()->cascadeOnDelete();
            $table->string('location_name');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('reported_at');
            $table->text('notes')->nullable();
            $table->boolean('is_customer_visible')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('container_location_updates');
    }
};
