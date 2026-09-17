<?php

/**
 * File: database/seeders/DemoCompanySeeder.php
 * Responsibility: Seeds demo companies and portal users for the many-to-many demo.
 * What it does:
 * - Creates five companies and five customer-portal users.
 * - Deliberately overlaps them so the many-to-many relationship from spec.md is
 *   visible in the UI: some users manage several companies, and some companies
 *   are managed by several users.
 * How to use: run by DatabaseSeeder (before DemoShipmentSeeder, which looks
 *   companies up by code).
 * How to extend: add a row to $companies or $portalUsers; the pivot is synced
 *   from the list of company codes.
 *
 * The seeded matrix (user -> companies they manage):
 *
 *   customer@example.com       Dewi Customer      NUS, SIN, BJM
 *   buyer@sinar.test           Budi Buyer         SIN
 *   rina@nusantara.test        Rina Hartono       NUS, JRD
 *   agus@borneo.test           Agus Pratama       BJM, SNI, JRD
 *   sari@java-retail.test      Sari Wijaya        JRD
 *
 * Operators (admin panel row scope — see User::companyIds):
 *
 *   operator@example.com       Operator           NUS, SNI
 *
 * Read it by company: NUS<-Dewi,Rina,Operator · SIN<-Dewi,Budi · BJM<-Dewi,Agus
 *                     SNI<-Agus,Operator · JRD<-Rina,Agus,Sari
 */

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoCompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [];

        foreach ($this->companies() as $code => $attributes) {
            $companies[$code] = Company::query()->updateOrCreate(['code' => $code], $attributes);
        }

        foreach ($this->portalUsers() as $email => [$name, $companyCodes]) {
            $user = User::query()->updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => 'password', 'is_active' => true],
            );

            $user->syncRoles([Role::CUSTOMER]);

            // sync() (not attach) keeps re-running the seeder idempotent.
            $user->companies()->sync(
                collect($companyCodes)->map(fn (string $code): int => $companies[$code]->getKey())->all(),
            );
        }

        // Operators are created by SuperAdminSeeder; only their company links
        // are set here. The links drive the admin panel row-level scope, so
        // operator@example.com deliberately sees a subset of the shipments.
        foreach ($this->operators() as $email => $companyCodes) {
            $user = User::query()->where('email', $email)->first();

            $user?->companies()->sync(
                collect($companyCodes)->map(fn (string $code): int => $companies[$code]->getKey())->all(),
            );
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function companies(): array
    {
        return [
            'NUS' => [
                'name' => 'PT Nusantara Ekspor',
                'email' => 'ops@nusantara.test',
                'phone' => '+62 21 555 0101',
                'address' => 'Jl. Pelabuhan No. 1, Jakarta',
                'is_active' => true,
            ],
            'SIN' => [
                'name' => 'PT Sinar Impor',
                'email' => 'ops@sinar.test',
                'phone' => '+62 21 555 0202',
                'address' => 'Jl. Industri No. 8, Surabaya',
                'is_active' => true,
            ],
            'BJM' => [
                'name' => 'CV Borneo Jaya Mandiri',
                'email' => 'ops@borneo.test',
                'phone' => '+62 542 555 0303',
                'address' => 'Jl. Soekarno Hatta No. 21, Balikpapan',
                'is_active' => true,
            ],
            'SNI' => [
                'name' => 'PT Sulawesi Nickel Industri',
                'email' => 'ops@sulawesi-nickel.test',
                'phone' => '+62 411 555 0404',
                'address' => 'Kawasan Industri Makassar, Makassar',
                'is_active' => true,
            ],
            'JRD' => [
                'name' => 'PT Java Retail Distribution',
                'email' => 'ops@java-retail.test',
                'phone' => '+62 31 555 0505',
                'address' => 'Jl. Rungkut Industri No. 12, Surabaya',
                'is_active' => true,
            ],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function operators(): array
    {
        return [
            'operator@example.com' => ['NUS', 'SNI'],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: array<int, string>}>
     */
    private function portalUsers(): array
    {
        return [
            'customer@example.com' => ['Dewi Customer', ['NUS', 'SIN', 'BJM']],
            'buyer@sinar.test' => ['Budi Buyer', ['SIN']],
            'rina@nusantara.test' => ['Rina Hartono', ['NUS', 'JRD']],
            'agus@borneo.test' => ['Agus Pratama', ['BJM', 'SNI', 'JRD']],
            'sari@java-retail.test' => ['Sari Wijaya', ['JRD']],
        ];
    }
}
