<?php

/**
 * File: tests/Feature/Admin/TableExportAndPruneTest.php
 * Responsibility: Guards the CSV export and prune-old-data table header actions.
 * What it does:
 * - Proves export is visible to admin/super_admin and hidden from operators,
 *   and that prune (permanent delete) is visible to super_admin only.
 * - Proves the prune service only deletes rows older than the 3-year window and
 *   takes their child rows (containers, logs) with them.
 * How to use: `php artisan test --filter=TableExportAndPruneTest`.
 * How to extend: add a table to the data providers when it gains the actions.
 */

namespace Tests\Feature\Admin;

use App\Filament\Concerns\TableExportColumns;
use App\Filament\Resources\Companies\Pages\EditCompany;
use App\Filament\Resources\Companies\Pages\ListCompanies;
use App\Filament\Resources\Companies\RelationManagers\ExportShipmentsRelationManager;
use App\Filament\Resources\Companies\RelationManagers\ImportShipmentsRelationManager;
use App\Filament\Resources\ExportContainers\Pages\ListExportContainers;
use App\Filament\Resources\ExportShipments\Pages\ListExportShipments;
use App\Filament\Resources\HsCodes\Pages\ListHsCodes;
use App\Filament\Resources\ImportContainers\Pages\ListImportContainers;
use App\Filament\Resources\ImportShipments\Pages\ListImportShipments;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Company;
use App\Models\ExportShipment;
use App\Models\Role;
use App\Models\User;
use App\Services\Prune\OldDataPruner;
use Filament\Facades\Filament;
use Filament\Tables\Columns\Column as TableColumn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Tests\TestCase;

class TableExportAndPruneTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    /**
     * @return array<string, array{0: class-string}>
     */
    public static function tableProvider(): array
    {
        return [
            'export shipments' => [ListExportShipments::class],
            'import shipments' => [ListImportShipments::class],
            'export containers' => [ListExportContainers::class],
            'import containers' => [ListImportContainers::class],
            'companies' => [ListCompanies::class],
            'users' => [ListUsers::class],
            'hs codes' => [ListHsCodes::class],
        ];
    }

    /**
     * @param  class-string  $page
     */
    #[DataProvider('tableProvider')]
    public function test_admin_sees_the_export_action_but_never_prune(string $page): void
    {
        $this->actingAs($this->user(Role::ADMIN));

        // An admin may soft-delete and restore, but permanent deletion (prune)
        // is reserved for super_admin.
        Livewire::test($page)
            ->assertTableActionVisible('export')
            ->assertTableActionHidden('prune-'.self::pruneKey($page));
    }

    /**
     * @param  class-string  $page
     */
    #[DataProvider('tableProvider')]
    public function test_super_admin_sees_the_export_and_prune_actions(string $page): void
    {
        $this->actingAs($this->user(Role::SUPER_ADMIN));

        Livewire::test($page)
            ->assertTableActionVisible('export')
            ->assertTableActionVisible('prune-'.self::pruneKey($page));
    }

    /**
     * @param  class-string  $page
     */
    #[DataProvider('tableProvider')]
    public function test_operator_never_sees_the_export_or_prune_actions(string $page): void
    {
        $this->actingAs($this->user(Role::OPERATOR));

        // Companies and users are admin-only resources: an operator is blocked
        // at the page level, which is even stronger than hiding the actions.
        if (in_array($page, [ListCompanies::class, ListUsers::class], true)) {
            $this->get($page::getResource()::getUrl('index'))->assertForbidden();

            return;
        }

        Livewire::test($page)
            ->assertTableActionHidden('export')
            ->assertTableActionHidden('prune-'.self::pruneKey($page));
    }

    private static function pruneKey(string $page): string
    {
        return match ($page) {
            ListExportShipments::class => 'export-shipments',
            ListImportShipments::class => 'import-shipments',
            ListExportContainers::class => 'export-containers',
            ListImportContainers::class => 'import-containers',
            ListCompanies::class => 'companies',
            ListUsers::class => 'users',
            ListHsCodes::class => 'hs-codes',
            default => throw new \InvalidArgumentException("No prune key for [{$page}]."),
        };
    }

    public function test_pruner_deletes_only_rows_older_than_three_years(): void
    {
        $pruner = app(OldDataPruner::class);

        $old = ExportShipment::query()->firstOrFail();
        $old->forceFill(['created_at' => now()->subYears(4)])->save();

        $fresh = ExportShipment::query()->whereKeyNot($old->getKey())->firstOrFail();
        $fresh->forceFill(['created_at' => now()->subYear()])->save();

        $this->assertSame(1, $pruner->countFor('export-shipments'));
        $this->assertSame(1, $pruner->pruneFor('export-shipments'));

        $this->assertDatabaseMissing('export_shipments', ['id' => $old->getKey()]);
        $this->assertDatabaseHas('export_shipments', ['id' => $fresh->getKey()]);
    }

    public function test_pruning_a_shipment_takes_its_containers_with_it(): void
    {
        $pruner = app(OldDataPruner::class);

        $shipment = ExportShipment::query()->whereHas('containers')->firstOrFail();
        $container = $shipment->containers()->firstOrFail();

        $shipment->forceFill(['created_at' => now()->subYears(4)])->save();

        $pruner->pruneFor('export-shipments');

        $this->assertDatabaseMissing('export_shipments', ['id' => $shipment->getKey()]);
        $this->assertDatabaseMissing('export_containers', ['id' => $container->getKey()]);
    }

    public function test_prune_action_deletes_old_rows_for_a_super_admin(): void
    {
        $this->actingAs($this->user(Role::SUPER_ADMIN));

        $old = ExportShipment::query()->firstOrFail();
        $old->forceFill(['created_at' => now()->subYears(4)])->save();

        Livewire::test(ListExportShipments::class)
            ->callTableAction('prune-export-shipments');

        $this->assertDatabaseMissing('export_shipments', ['id' => $old->getKey()]);
    }

    public function test_export_columns_cover_every_field_and_relationship(): void
    {
        $exportShipmentColumns = array_keys(
            collect(TableExportColumns::for(TableExportColumns::EXPORT_SHIPMENTS))
                ->keyBy(fn (Column $column): string => $column->getName())
                ->all()
        );

        // Stored fields are all present.
        foreach (['bl_number', 'company_name_snapshot', 'aju_number', 'depot_closing_at', 'deleted_at'] as $field) {
            $this->assertContains($field, $exportShipmentColumns);
        }

        // Relationships are present too.
        foreach (['company.name', 'containers.container_number'] as $relation) {
            $this->assertContains($relation, $exportShipmentColumns);
        }

        $userColumns = collect(TableExportColumns::for(TableExportColumns::USERS))
            ->map(fn (Column $column): string => $column->getName())
            ->all();

        $this->assertContains('roles.name', $userColumns);
        $this->assertContains('companies.name', $userColumns);
        $this->assertContains('is_active', $userColumns);
        // Passwords must never leave the system.
        $this->assertNotContains('password', $userColumns);

        $containerColumns = collect(TableExportColumns::for(TableExportColumns::EXPORT_CONTAINERS))
            ->map(fn (Column $column): string => $column->getName())
            ->all();

        $this->assertContains('shipment.bl_number', $containerColumns);
        $this->assertContains('shipment.company.name', $containerColumns);
        $this->assertContains('vgm_value', $containerColumns);
    }

    public function test_export_renders_relationship_values_into_one_cell(): void
    {
        $shipment = ExportShipment::query()->whereHas('containers')->with('containers')->firstOrFail();
        $numbers = $shipment->containers->pluck('container_number')->filter()->implode(', ');

        $column = collect(TableExportColumns::for(TableExportColumns::EXPORT_SHIPMENTS))
            ->firstWhere(fn (Column $c): bool => $c->getName() === 'containers.container_number');

        $this->assertNotNull($column);

        $reflection = new \ReflectionProperty($column, 'getStateUsing');
        $reflection->setAccessible(true);
        $closure = $reflection->getValue($column)->getClosure();

        $this->assertSame($numbers, $closure($shipment));
    }

    public function test_export_action_actually_produces_a_file_with_relationships(): void
    {
        $this->actingAs($this->user(Role::ADMIN));

        $shipment = ExportShipment::query()->whereHas('containers')->with('containers')->firstOrFail();

        $component = Livewire::test(ListExportShipments::class);
        $export = ExcelExport::make('export')
            ->withColumns(TableExportColumns::for(TableExportColumns::EXPORT_SHIPMENTS))
            ->hydrate($component->instance());

        $row = $export->map($shipment);

        $this->assertSame($shipment->bl_number, $row['bl_number']);
        $this->assertSame($shipment->company->name, $row['company.name']);
        $this->assertSame(
            $shipment->containers->pluck('container_number')->filter()->implode(', '),
            $row['containers.container_number'],
        );
        $this->assertArrayHasKey('deleted_at', $row);
        $this->assertArrayNotHasKey('password', $row);
    }

    public function test_shipment_tables_show_created_and_updated_instead_of_eta(): void
    {
        $this->actingAs($this->user(Role::ADMIN));

        foreach ([ListExportShipments::class, ListImportShipments::class] as $page) {
            $names = array_map(
                fn (TableColumn $column): string => $column->getName(),
                Livewire::test($page)->instance()->getTable()->getColumns(),
            );

            $this->assertContains('created_at', $names, "{$page} should show Created");
            $this->assertContains('updated_at', $names, "{$page} should show Updated");
            $this->assertNotContains('eta_at', $names, "{$page} should not show ETA");
            $this->assertContains('port_of_loading', $names, "{$page} should show Loading");
            $this->assertContains('port_of_discharge', $names, "{$page} should show Discharge");
        }
    }

    public function test_company_shipment_relation_managers_show_created_and_updated(): void
    {
        $this->actingAs($this->user(Role::ADMIN));

        $company = Company::query()->firstOrFail();

        foreach ([ExportShipmentsRelationManager::class, ImportShipmentsRelationManager::class] as $relationManager) {
            $names = array_map(
                fn (TableColumn $column): string => $column->getName(),
                Livewire::test($relationManager, [
                    'ownerRecord' => $company,
                    'pageClass' => EditCompany::class,
                ])->instance()->getTable()->getColumns(),
            );

            $this->assertContains('created_at', $names);
            $this->assertContains('updated_at', $names);
            $this->assertNotContains('eta_at', $names);
            $this->assertContains('port_of_loading', $names);
            $this->assertContains('port_of_discharge', $names);
        }
    }
}
