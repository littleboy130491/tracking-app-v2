<?php

/**
 * File: database/migrations/2026_09_15_040017_create_activity_logs_table.php
 * Responsibility: Creates the append-only `activity_logs` table.
 * What it does:
 * - Records what changed, when, by whom, plus a customer-safe summary.
 * - Links each entry to an export/import shipment and (optionally) to one of
 *   its containers; entries about companies or users leave all four null.
 * How to use: App\Models\ActivityLog; written by App\Services\ActivityLogger.
 * How to extend: Add new event types; never update or delete existing rows.
 *
 * Note: the four nullable links exist because the domain is split per process.
 * Exactly one shipment link is set per row; a container link, when present,
 * matches the shipment's process.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('export_shipment_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('import_shipment_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('export_container_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('import_container_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 100);
            $table->string('entity_type', 100);
            $table->bigInteger('entity_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('customer_summary')->nullable();
            $table->boolean('is_customer_visible')->default(false);
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['export_shipment_id', 'occurred_at']);
            $table->index(['import_shipment_id', 'occurred_at']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
