<?php

/**
 * File: app/Services/YearOptions.php
 * Responsibility: The distinct years present in a date column, as dropdown options.
 * What it does:
 * - Runs one aggregate query per table using the expression the connected
 *   database understands (SQLite, MySQL/MariaDB, Postgres, SQL Server); an
 *   unknown driver falls back to reading the column and mapping in PHP.
 * - Returns year => year pairs, newest first, ready for a filter or a select.
 * How to use: YearOptions::forQuery($table->getQuery(), 'created_at').
 * How to extend: add a driver arm to expression() when a new database is deployed.
 */

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;

final class YearOptions
{
    /**
     * @return array<string, string>
     */
    public static function forQuery(Builder|Relation $query, string $column = 'created_at'): array
    {
        $driver = $query instanceof Relation
            ? $query->getRelated()->getConnection()->getDriverName()
            : $query->getModel()->getConnection()->getDriverName();

        if (($expression = self::expression($driver, $column)) !== null) {
            return $query
                ->selectRaw("distinct {$expression} as year")
                ->orderByDesc('year')
                ->pluck('year', 'year')
                ->filter(fn (mixed $year): bool => filled($year))
                ->mapWithKeys(fn (mixed $year): array => [(string) $year => (string) $year])
                ->all();
        }

        // Unknown driver: read the column and bucket the years in PHP.
        return $query
            ->pluck($column)
            ->filter()
            ->map(fn (mixed $value): int => Carbon::parse($value)->year)
            ->unique()
            ->sortDesc()
            ->mapWithKeys(fn (int $year): array => [(string) $year => (string) $year])
            ->all();
    }

    /**
     * The database expression that extracts the year from a date column, or
     * null when the driver is unknown and the caller has to fall back to PHP.
     */
    public static function expression(string $driver, string $column): ?string
    {
        return match ($driver) {
            'sqlite' => "strftime('%Y', {$column})",
            'mysql', 'mariadb' => "YEAR({$column})",
            'pgsql' => "EXTRACT(YEAR FROM {$column})",
            'sqlsrv' => "YEAR({$column})",
            default => null,
        };
    }
}
