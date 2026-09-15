<?php

/**
 * File: database/seeders/HsCodeSeeder.php
 * Responsibility: Seeds a starter set of HS codes (shared master data).
 * What it does:
 * - Creates the codes the demo shipments attach; idempotent via
 *   updateOrCreate on the unique code.
 * How to use: run by DatabaseSeeder before DemoShipmentSeeder.
 * How to extend: append a code/description pair to the list.
 */

namespace Database\Seeders;

use App\Models\HsCode;
use Illuminate\Database\Seeder;

class HsCodeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->codes() as $code => $description) {
            HsCode::query()->updateOrCreate(['code' => $code], ['description' => $description]);
        }
    }

    /**
     * @return array<string, string>
     */
    private function codes(): array
    {
        return [
            '9403.60' => 'Wooden furniture, other',
            '8542.31' => 'Electronic integrated circuits: processors and controllers',
            '4407.99' => 'Wood sawn or chipped lengthwise, other',
            '2604.00' => 'Nickel ores and concentrates',
            '8450.11' => 'Household washing machines, fully automatic',
        ];
    }
}
