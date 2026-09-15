<?php

/**
 * File: app/Providers/AppServiceProvider.php
 * Responsibility: Application-wide service registration and small global fixes.
 * What it does:
 * - Replaces Laravel's UUID generator.
 *   `Str::orderedUuid()` uses ramsey/uuid's CombGenerator, which crashes the PHP
 *   process (SIGILL) on this environment. Filament calls it for every
 *   notification id, so the admin panel could not save anything. Laravel 13 uses
 *   one `$uuidFactory` for uuid(), uuid7() and orderedUuid(), so overriding it
 *   here fixes all three without patching vendor.
 *   The replacement builds a v7-shaped, time-sortable id from random_bytes.
 * How to use: automatic; nothing needs to call it.
 * How to extend: add further service bindings in register().
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->useSafeUuidGenerator();
    }

    /**
     * A 48-bit millisecond timestamp followed by random bits, shaped like a v7
     * UUID (8-4-4-4-12, with the version and variant nibbles set) so ids stay
     * unique and sort roughly by creation time.
     */
    private function useSafeUuidGenerator(): void
    {
        Str::createUuidsUsing(function (): string {
            $timestamp = sprintf('%012x', (int) floor(microtime(true) * 1000));
            $random = bin2hex(random_bytes(10)); // 20 hex characters

            return sprintf(
                '%s-%s-%s-%s-%s',
                substr($timestamp, 0, 8),
                substr($timestamp, 8, 4),
                '7'.substr($random, 0, 3),
                dechex(8 | (hexdec($random[3]) & 3)).substr($random, 4, 3),
                substr($random, 7, 12),
            );
        });
    }
}
