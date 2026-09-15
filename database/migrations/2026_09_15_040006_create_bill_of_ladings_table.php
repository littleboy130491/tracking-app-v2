<?php

/**
 * File: database/migrations/2026_09_15_040006_create_bill_of_ladings_table.php
 * Responsibility: Creates the `bill_of_ladings` table (shared shipment data).
 * What it does:
 * - Stores header data that applies to every container in one B/L.
 * - Keeps billing/draft-PIB confirmation state and soft deletes.
 * How to use: App\Models\BillOfLading hasMany Container / belongsToMany HsCode.
 * How to extend: Add new shared operational fields as nullable columns.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_of_ladings', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 100)->unique();
            $table->string('bl_number', 100)->nullable()->index();
            $table->string('shipment_type', 20);
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('company_name_snapshot');

            $table->string('aju_number', 100)->nullable();
            $table->string('do_number', 100)->nullable();
            $table->string('shipping_line')->nullable();
            $table->string('vessel_name')->nullable();
            $table->string('voyage_number', 100)->nullable();
            $table->string('port_of_loading')->nullable();
            $table->string('port_of_discharge')->nullable();
            $table->timestamp('depot_closing_at')->nullable();
            $table->timestamp('cy_closing_at')->nullable();
            $table->date('departure_date')->nullable();
            $table->timestamp('eta_at')->nullable();
            $table->timestamp('actual_arrival_at')->nullable();

            $table->text('goods_description')->nullable();
            $table->integer('package_count')->nullable();
            $table->string('package_unit', 50)->nullable();
            $table->string('terminal_name')->nullable();
            $table->date('loading_date')->nullable();
            $table->text('loading_destination')->nullable();

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
            $table->timestamp('completed_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['shipment_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_of_ladings');
    }
};
