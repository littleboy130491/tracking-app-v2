<?php

/**
 * File: database/migrations/2026_09_15_040017_create_activity_logs_table.php
 * Responsibility: Creates the append-only `activity_logs` table.
 * What it does:
 * - Records what changed, when, by whom, plus a customer-safe summary.
 * - is_customer_visible controls what the customer dashboard may show.
 * How to use: App\Models\ActivityLog; written by App\Services\ActivityLogger.
 * How to extend: Add new event types; never update or delete existing rows.
 *
 * Note: spatie/laravel-activitylog was evaluated but rejected because this
 * schema (bill_of_lading_id, container_id, customer_summary,
 * is_customer_visible, occurred_at) is fixed by migration_plan.md §10.
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
            $table->foreignId('bill_of_lading_id')->constrained()->cascadeOnDelete();
            $table->foreignId('container_id')->nullable()->constrained()->cascadeOnDelete();
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

            $table->index(['bill_of_lading_id', 'occurred_at']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
