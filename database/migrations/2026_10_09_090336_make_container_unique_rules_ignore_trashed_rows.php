<?php

/**
 * File: database/migrations/2026_10_09_090336_make_container_unique_rules_ignore_trashed_rows.php
 * Responsibility: Makes per-shipment container-number uniqueness ignore soft-deleted rows.
 * What it does:
 * - Replaces the plain unique index on (shipment FK, container_number) with a
 *   partial one that only applies to active (deleted_at IS NULL) rows.
 * - Fixes the production crash: re-adding a container number whose earlier row
 *   was soft-deleted failed with "UNIQUE constraint failed".
 * How to use: php artisan migrate (fails if a table already holds active
 *   duplicates — resolve those rows first).
 * How to extend: give another soft-deleting table the same rule by adding it
 *   to TABLES.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Table => shipment FK column sharing the same unique rule. */
    private const TABLES = [
        'export_containers' => 'export_shipment_id',
        'import_containers' => 'import_shipment_id',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table => $shipmentKey) {
            $index = "{$table}_{$shipmentKey}_container_number_unique";

            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropUnique([$shipmentKey, 'container_number']));

            // Laravel's schema builder cannot express partial indexes, so the
            // soft-delete-aware variant is created with raw SQL (SQLite has
            // supported partial indexes since 3.8).
            DB::statement(
                "create unique index \"{$index}\" on \"{$table}\" (\"{$shipmentKey}\", \"container_number\") where \"deleted_at\" is null",
            );
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table => $shipmentKey) {
            $index = "{$table}_{$shipmentKey}_container_number_unique";

            DB::statement("drop index \"{$index}\"");

            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->unique([$shipmentKey, 'container_number'], $index));
        }
    }
};
