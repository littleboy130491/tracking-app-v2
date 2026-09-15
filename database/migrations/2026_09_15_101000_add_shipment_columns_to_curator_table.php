<?php

/**
 * File: database/migrations/2026_09_15_101000_add_shipment_columns_to_curator_table.php
 * Responsibility: Links Curator media to shipments and retires the old attachments table.
 * What it does:
 * - Adds the domain columns from migration_plan.md §10 to Curator's `curator`
 *   table (which holds the file metadata, storage path and URL generation), so
 *   one table serves as both the media library and the attachment record.
 * - Drops the previous hand-rolled `attachments` table, which never had a
 *   working upload path and is superseded by Curator.
 * How to use: `php artisan migrate`; the model is App\Models\Attachment.
 * How to extend: add further linkage columns here rather than in a new table.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curator', function (Blueprint $table) {
            $table->foreignId('bill_of_lading_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->foreignId('container_id')->nullable()->after('bill_of_lading_id')->constrained()->cascadeOnDelete();
            $table->string('category', 50)->nullable()->after('container_id');
            $table->boolean('is_customer_visible')->default(false)->after('category');
            $table->foreignId('uploaded_by')->nullable()->after('is_customer_visible')->constrained('users')->nullOnDelete();

            $table->index(['bill_of_lading_id', 'category']);
        });

        // Superseded by Curator; it was never written to as there was no upload path.
        Schema::dropIfExists('attachments');
    }

    public function down(): void
    {
        Schema::table('curator', function (Blueprint $table) {
            $table->dropIndex(['bill_of_lading_id', 'category']);
            $table->dropConstrainedForeignId('uploaded_by');
            $table->dropColumn('is_customer_visible');
            $table->dropColumn('category');
            $table->dropConstrainedForeignId('container_id');
            $table->dropConstrainedForeignId('bill_of_lading_id');
        });
    }
};
