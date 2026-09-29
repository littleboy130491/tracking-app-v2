<?php

/**
 * File: tests/Unit/YearOptionsTest.php
 * Responsibility: Guards the per-driver year expression behind the date filters.
 * What it does:
 * - Asserts the SQL expression chosen for every supported database driver and
 *   that an unknown driver returns null so the caller falls back to PHP.
 * How to use: `php artisan test --filter=YearOptionsTest`.
 * How to extend: add a case when a new database driver is supported.
 */

namespace Tests\Unit;

use App\Services\YearOptions;
use PHPUnit\Framework\TestCase;

class YearOptionsTest extends TestCase
{
    public function test_every_supported_driver_gets_its_own_year_expression(): void
    {
        $this->assertSame("strftime('%Y', created_at)", YearOptions::expression('sqlite', 'created_at'));
        $this->assertSame('YEAR(created_at)', YearOptions::expression('mysql', 'created_at'));
        $this->assertSame('YEAR(created_at)', YearOptions::expression('mariadb', 'created_at'));
        $this->assertSame('EXTRACT(YEAR FROM created_at)', YearOptions::expression('pgsql', 'created_at'));
        $this->assertSame('YEAR(created_at)', YearOptions::expression('sqlsrv', 'created_at'));
    }

    public function test_an_unknown_driver_falls_back_to_php(): void
    {
        $this->assertNull(YearOptions::expression('mongodb', 'created_at'));
    }

    public function test_the_column_name_is_used_in_the_expression(): void
    {
        $this->assertSame('YEAR(shipped_at)', YearOptions::expression('mysql', 'shipped_at'));
    }
}
