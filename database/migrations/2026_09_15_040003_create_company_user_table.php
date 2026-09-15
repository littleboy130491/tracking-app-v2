<?php

/**
 * File: database/migrations/2026_09_15_040003_create_company_user_table.php
 * Responsibility: Pivot table linking users to the companies they handle.
 * What it does:
 * - Implements the many-to-many relationship from spec.md (a user can handle
 *   multiple companies and a company can be handled by multiple users).
 * - Composite primary key on (company_id, user_id); no surrogate id.
 * How to use: `$user->companies` and `$company->users` (withTimestamps).
 * How to extend: Add relationship metadata (e.g. "primary contact") here.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_user', function (Blueprint $table) {
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['company_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_user');
    }
};
