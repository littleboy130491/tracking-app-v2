<?php

/**
 * File: database/migrations/2026_09_15_040006_create_import_shipments_table.php
 * Responsibility: Creates the `import_shipments` table (import process header).
 * What it does:
 * - Stores the header data shared by every container of one import shipment,
 *   one column group per IMPORT.md milestone: document checking, PIB
 *   confirmation, billing, sailing dates and goods description.
 * How to use: App\Models\ImportShipment hasMany ImportContainer; belongsToMany HsCode.
 * How to extend: Add new import fields as nullable columns and expose them in
 *   ImportShipmentForm.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_shipments', function (Blueprint $table) {
            $table->id();
            // Process 1 — Document received / Checking document.
            $table->string('bl_number', 100)->nullable()->index();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('company_name_snapshot');
            $table->date('document_received_date')->nullable();
            $table->foreignId('document_received_by')->nullable()->constrained('users')->nullOnDelete();

            // Process 2 — Draft PIB / Checking draft PIB to importir.
            $table->string('shipping_line')->nullable();
            $table->string('vessel_name')->nullable();
            // Waiting confirmation from customer.
            $table->boolean('confirmation_checklist')->default(false);
            // Final sending PIB to custom (issuing billing).
            $table->string('aju_number', 100)->nullable();
            $table->string('voyage_number', 100)->nullable();
            $table->string('billing_issuance_status', 30)->default('not_issued');
            // Process payment THC / Waiting release DO / DO release.
            $table->string('port_of_loading')->nullable();
            $table->date('departure_date')->nullable();
            $table->string('port_of_discharge')->nullable();
            // Payment billing (arrival time / ETA) / Response billing.
            $table->timestamp('eta_at')->nullable();
            $table->string('billing_response', 20)->nullable();
            // Upload all document.
            $table->text('goods_description')->nullable();
            // Containers tab header: cargo and loading data.
            $table->string('packages')->nullable();
            // Container shipping schedule.
            $table->string('terminal_name')->nullable();
            $table->date('loading_date')->nullable();
            $table->text('loading_destination')->nullable();

            $table->string('status', 30)->default('draft');
            $table->string('current_milestone', 40)->default('document_received');
            $table->string('latest_event', 40)->nullable();
            $table->timestamp('latest_event_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_shipments');
    }
};
