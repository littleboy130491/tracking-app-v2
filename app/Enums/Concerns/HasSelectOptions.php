<?php

/**
 * File: app/Enums/Concerns/HasSelectOptions.php
 * Responsibility: Shared helpers for backed enums used in forms and filters.
 * What it does:
 * - `options()` returns value => label pairs for Filament select/radio fields.
 * - `values()` returns the raw backed values for validation rules.
 * How to use: `use HasSelectOptions;` inside a string-backed enum that defines label().
 * How to extend: Add helpers such as `labels()` or a `fromLabel()` lookup here.
 */

namespace App\Enums\Concerns;

trait HasSelectOptions
{
    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (\BackedEnum $case): string => (string) $case->value, self::cases());
    }
}
