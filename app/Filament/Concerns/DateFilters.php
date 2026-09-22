<?php

/**
 * File: app/Filament/Concerns/DateFilters.php
 * Responsibility: Builds the shared Year / Month / date-range table filters for a model's date column.
 * What it does:
 * - Returns three Filament filters — `created_year`, `created_month` and
 *   `created_between` (From/Until date pickers) — that narrow a list by the
 *   record's date column, defaulting to `created_at`.
 * - Year options list the years actually stored in the model's table (SQLite
 *   strftime, the same approach as the customer portal dashboard).
 * - Used by the B/L, container and company relation-manager tables so every
 *   admin list filters dates the same way.
 * How to use: `...DateFilters::make(ImportShipment::class),` inside `->filters([...])`.
 * How to extend: pass another `$column`/`$label`; the three filters follow.
 */

declare(strict_types=1);

namespace App\Filament\Concerns;

use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DateFilters
{
    /**
     * Month number => English name, used by every Month dropdown.
     *
     * @var array<int, string>
     */
    public const MONTHS = [
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ];

    /**
     * The three date filters for one model's date column.
     *
     * Filter keys: `created_year`, `created_month`, `created_between`.
     *
     * @param  class-string<Model>  $modelClass  Source of the Year dropdown options.
     * @param  string  $column  Date column the filters apply to.
     * @param  string  $label  Label of the From/Until range filter.
     * @return array<int, SelectFilter|Filter>
     */
    public static function make(string $modelClass, string $column = 'created_at', string $label = 'Created'): array
    {
        return [
            self::yearFilter($modelClass, $column),
            self::monthFilter($column),
            self::rangeFilter($column, $label),
        ];
    }

    /**
     * Dropdown of the years that actually exist in the table, newest first.
     *
     * @param  class-string<Model>  $modelClass
     */
    private static function yearFilter(string $modelClass, string $column): SelectFilter
    {
        return SelectFilter::make('created_year')
            ->label('Year')
            ->options(fn (): array => $modelClass::query()
                ->selectRaw("distinct strftime('%Y', {$column}) as year")
                ->orderByDesc('year')
                ->pluck('year', 'year')
                ->filter()
                ->all())
            ->query(fn (Builder $query, array $data): Builder => $query->when(
                $data['value'] ?? null,
                fn (Builder $inner, mixed $year): Builder => $inner->whereYear($column, (int) $year),
            ));
    }

    /**
     * Month dropdown (January–December), usable alone or together with Year.
     */
    private static function monthFilter(string $column): SelectFilter
    {
        return SelectFilter::make('created_month')
            ->label('Month')
            ->options(self::MONTHS)
            ->query(fn (Builder $query, array $data): Builder => $query->when(
                $data['value'] ?? null,
                fn (Builder $inner, mixed $month): Builder => $inner->whereMonth($column, (int) $month),
            ));
    }

    /**
     * Inclusive From/Until range on the date column.
     */
    private static function rangeFilter(string $column, string $label): Filter
    {
        return Filter::make('created_between')
            ->label("{$label} between")
            ->schema([
                DatePicker::make('from')->label('From'),
                DatePicker::make('until')->label('Until'),
            ])
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when($data['from'] ?? null, fn (Builder $inner, string $date): Builder => $inner->whereDate($column, '>=', $date))
                ->when($data['until'] ?? null, fn (Builder $inner, string $date): Builder => $inner->whereDate($column, '<=', $date)));
    }
}
