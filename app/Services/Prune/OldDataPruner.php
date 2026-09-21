<?php

/**
 * File: app/Services/Prune/OldDataPruner.php
 * Responsibility: Permanently deletes records older than the retention window.
 * What it does:
 * - Resolves the retention cutoff (3 years) and the models a table may purge.
 * - countFor() tells the UI how many rows would be deleted (no side effects).
 * - pruneFor() force-deletes those rows; child rows (containers, HS-code pivots,
 *   activity logs, portal attachments) go with them via the FK cascade.
 * How to use: `app(OldDataPruner::class)->pruneFor($key)` from the table action.
 * How to extend: add a key to MANIFEST to make another model prunable, then add
 *   the matching action to that table.
 */

declare(strict_types=1);

namespace App\Services\Prune;

use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\ExportContainers\ExportContainerResource;
use App\Filament\Resources\ExportShipments\ExportShipmentResource;
use App\Filament\Resources\HsCodes\HsCodeResource;
use App\Filament\Resources\ImportContainers\ImportContainerResource;
use App\Filament\Resources\ImportShipments\ImportShipmentResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Company;
use App\Models\ExportContainer;
use App\Models\ExportShipment;
use App\Models\HsCode;
use App\Models\ImportContainer;
use App\Models\ImportShipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class OldDataPruner
{
    /**
     * Records must be at least this old before they may be purged.
     */
    public const RETENTION_YEARS = 3;

    /**
     * Prunable models keyed by a short name. The key is reused by the tables
     * (via PrunableTableHeaderAction) and in tests.
     *
     * @var array<string, class-string<Model>>
     */
    private const MANIFEST = [
        'export-shipments' => ExportShipment::class,
        'import-shipments' => ImportShipment::class,
        'export-containers' => ExportContainer::class,
        'import-containers' => ImportContainer::class,
        'companies' => Company::class,
        'users' => User::class,
        'hs-codes' => HsCode::class,
    ];

    /**
     * Human labels used in the confirmation dialog.
     *
     * @var array<string, string>
     */
    private const LABELS = [
        'export-shipments' => 'export bill of ladings',
        'import-shipments' => 'import bill of ladings',
        'export-containers' => 'export containers',
        'import-containers' => 'import containers',
        'companies' => 'companies',
        'users' => 'users',
        'hs-codes' => 'HS codes',
    ];

    /**
     * Plural label for a prunable key, e.g. "export containers".
     */
    public static function labelFor(string $key): string
    {
        return self::LABELS[$key] ?? $key;
    }

    /**
     * @return class-string<Model>
     */
    public static function modelFor(string $key): string
    {
        return self::MANIFEST[$key] ?? throw new InvalidArgumentException("Unknown prunable key [{$key}].");
    }

    /**
     * How many rows of this model are older than the retention window.
     */
    public function countFor(string $key): int
    {
        return $this->query($key)->count();
    }

    /**
     * Permanently remove every row older than the retention window and return
     * how many parent records were deleted.
     */
    public function pruneFor(string $key): int
    {
        $query = $this->query($key);

        // Force-delete so soft-deleted rows (shipments, containers) are removed
        // for good; plain models are deleted normally.
        $deleted = method_exists(self::modelFor($key), 'forceDelete')
            ? $query->forceDelete()
            : $query->delete();

        return (int) $deleted;
    }

    /**
     * The cutoff timestamp: "older than" means created_at < cutoff.
     */
    public function cutoff(): Carbon
    {
        return now()->subYears(self::RETENTION_YEARS);
    }

    /**
     * @return Builder<Model>
     */
    private function query(string $key): Builder
    {
        if (! array_key_exists($key, self::MANIFEST)) {
            throw new InvalidArgumentException("Unknown prunable key [{$key}].");
        }

        return self::modelFor($key)::query()
            ->where('created_at', '<', $this->cutoff());
    }

    /**
     * The table-header action class each resource should expose, kept here so a
     * single key map drives both the UI and the service.
     *
     * @return array<string, class-string>
     */
    public static function resourceMap(): array
    {
        return [
            'export-shipments' => ExportShipmentResource::class,
            'import-shipments' => ImportShipmentResource::class,
            'export-containers' => ExportContainerResource::class,
            'import-containers' => ImportContainerResource::class,
            'companies' => CompanyResource::class,
            'users' => UserResource::class,
            'hs-codes' => HsCodeResource::class,
        ];
    }
}
