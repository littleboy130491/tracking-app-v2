<?php

/**
 * File: tests/Feature/Seeders/SeederIdempotencyTest.php
 * Responsibility: Guards the demo seeders against non-idempotent writes.
 * What it does:
 * - Seeds the database twice and asserts the second run changes nothing: no
 *   duplicated rows and, importantly, no extra audit entries (a seeder that
 *   resets status columns on live shipments).
 * How to use: `php artisan test --filter=SeederIdempotencyTest`.
 * How to extend: add a new seeded aggregate to the snapshot when one appears.
 */

namespace Tests\Feature\Seeders;

use App\Enums\ShipmentStatus;
use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\ExportContainer;
use App\Models\ExportShipment;
use App\Models\ImportContainer;
use App\Models\ImportShipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_running_the_seeders_twice_changes_nothing(): void
    {
        $this->seed();

        $before = $this->snapshot();

        $this->seed();

        $this->assertSame($before, $this->snapshot());
    }

    public function test_completed_shipments_stay_completed_and_keep_their_timestamp(): void
    {
        $this->seed();

        $completed = $this->completedShipmentTimestamps();

        $this->assertCount(3, $completed, 'The seeders should produce three completed shipments.');

        $this->seed();

        $this->assertSame($completed, $this->completedShipmentTimestamps());
    }

    /**
     * @return array<string, string|null>
     */
    private function completedShipmentTimestamps(): array
    {
        $timestamps = [];

        foreach ([ExportShipment::class, ImportShipment::class] as $model) {
            foreach ($model::query()->where('status', ShipmentStatus::Completed)->get() as $shipment) {
                $timestamps[$shipment->bl_number] = $shipment->completed_at?->toDateTimeString();
            }
        }

        return $timestamps;
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(): array
    {
        return [
            'companies' => Company::query()->count(),
            'users' => User::query()->count(),
            'export_shipments' => ExportShipment::query()->count(),
            'import_shipments' => ImportShipment::query()->count(),
            'export_containers' => ExportContainer::query()->count(),
            'import_containers' => ImportContainer::query()->count(),
            'activity_logs' => ActivityLog::query()->count(),
            'completed_shipments' => ExportShipment::query()->where('status', ShipmentStatus::Completed)->count()
                + ImportShipment::query()->where('status', ShipmentStatus::Completed)->count(),
        ];
    }
}
