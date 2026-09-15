<?php

/**
 * File: database/migrations/2026_09_15_040018_add_is_internal_to_roles_table.php
 * Responsibility: Adds an internal/customer flag to the spatie roles table.
 * What it does:
 * - Distinguishes staff roles (admin, operator) from the customer portal role.
 * - migration_plan.md §3 defined a custom `roles` table with `is_internal`;
 *   spatie/laravel-permission is the role source of truth (Filament Shield), so
 *   only that extra flag is added here.
 * How to use: App\Models\Role extends the spatie Role model and casts this flag.
 * How to extend: Add display labels or ordering columns to the same table.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('is_internal')->default(true)->after('guard_name');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('is_internal');
        });
    }
};
