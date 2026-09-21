<?php

/**
 * File: database/migrations/2026_09_15_040001_create_companies_table.php
 * Responsibility: Creates the `companies` table (companies that own shipments).
 * What it does:
 * - Stores identity/contact data for a customer company.
 * - `code` is an optional unique short code; `is_active` toggles access.
 * - Soft-deletable: admin/super_admin may remove and restore; only
 *   super_admin may permanently delete.
 * How to use: Run with `php artisan migrate`; model is App\Models\Company.
 * How to extend: Add new company-level attributes as nullable columns here.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->nullable()->unique();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
