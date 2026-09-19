<?php

/**
 * File: database/migrations/2026_09_15_040006_create_export_shipments_table.php
 * Responsibility: Creates the `export_shipments` table (export process header).
 * What it does:
 * - Stores the header data shared by every container of one export shipment:
 *   booking order, pickup/stuffing, sailing dates and status/milestone state.
 * How to use: App\Models\ExportShipment hasMany ExportContainer; belongsToMany HsCode.
 * How to extend: Add new export fields as nullable columns and expose them in
 *   ExportShipmentForm.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('export_shipments', function (Blueprint $table) {
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
            $table->timestamp('depot_closing_at')->nullable();
            $table->timestamp('cy_closing_at')->nullable();
            $table->string('pickup_depot_name')->nullable();
            $table->timestamp('stuffing_date')->nullable();
            $table->text('stuffing_destination')->nullable();
            $table->date('departure_date')->nullable();
            $table->timestamp('eta_at')->nullable();
            $table->timestamp('actual_arrival_at')->nullable();
            $table->text('goods_description')->nullable();

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
        Schema::dropIfExists('export_shipments');
    }
};
