<?php

/**
 * File: database/migrations/2026_09_15_040006_create_import_shipments_table.php
 * Responsibility: Creates the `import_shipments` table (import process header).
 * What it does:
 * - Stores the header data shared by every container of one import shipment:
 *   document checking, PIB confirmation, billing/THC/behandle payments,
 *   DO release and sailing dates.
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
            $table->string('bl_number', 100)->nullable()->index();
            $table->string('shipment_mode', 20)->nullable();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('company_name_snapshot');
            $table->date('document_received_date')->nullable();
            $table->foreignId('document_received_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('aju_number', 100)->nullable();
            $table->string('do_number', 100)->nullable();
            $table->string('shipping_line')->nullable();
            $table->string('vessel_name')->nullable();
            $table->string('voyage_number', 100)->nullable();
            $table->string('port_of_loading')->nullable();
            $table->string('port_of_discharge')->nullable();
            $table->date('departure_date')->nullable();
            $table->timestamp('eta_at')->nullable();
            $table->timestamp('actual_arrival_at')->nullable();
            $table->text('goods_description')->nullable();

            $table->string('draft_pib_confirmation_status', 30)->default('pending');
            $table->timestamp('draft_pib_confirmed_at')->nullable();
            $table->text('draft_pib_confirmation_notes')->nullable();

            $table->string('billing_issuance_status', 30)->default('not_issued');
            $table->timestamp('billing_issued_at')->nullable();
            $table->string('billing_payment_status', 30)->default('not_paid');
            $table->timestamp('billing_paid_at')->nullable();
            $table->string('billing_response', 20)->nullable();
            $table->timestamp('billing_response_at')->nullable();

            $table->string('thc_payment_status', 30)->default('not_paid');
            $table->timestamp('thc_paid_at')->nullable();
            $table->string('behandle_payment_status', 30)->nullable();
            $table->timestamp('behandle_paid_at')->nullable();
            $table->timestamp('do_released_at')->nullable();

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
