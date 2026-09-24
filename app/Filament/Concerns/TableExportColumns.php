<?php

/**
 * File: app/Filament/Concerns/TableExportColumns.php
 * Responsibility: Builds the full CSV column set for each prunable resource table.
 * What it does:
 * - Lists every stored field plus the relationship fields (company, shipment,
 *   containers, HS codes, roles, received-by) so a CSV holds
 *   the complete record, not just the on-screen columns.
 * - Flattens related rows into a single cell (comma separated / newline safe).
 * How to use: `TableExportColumns::for(TableExportColumns::EXPORT_SHIPMENTS)`
 *   inside `ExportAction::make()->exports([...])`.
 * How to extend: add a resource key + method; both the table and tests use it.
 */

declare(strict_types=1);

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;
use pxlrbt\FilamentExcel\Columns\Column;

class TableExportColumns
{
    public const EXPORT_SHIPMENTS = 'export-shipments';

    public const IMPORT_SHIPMENTS = 'import-shipments';

    public const EXPORT_CONTAINERS = 'export-containers';

    public const IMPORT_CONTAINERS = 'import-containers';

    public const COMPANIES = 'companies';

    public const USERS = 'users';

    public const HS_CODES = 'hs-codes';

    /**
     * Full column set for a key, built once and cached per request.
     *
     * @return array<int, Column>
     */
    public static function for(string $key): array
    {
        return match ($key) {
            self::EXPORT_SHIPMENTS => self::exportShipments(),
            self::IMPORT_SHIPMENTS => self::importShipments(),
            self::EXPORT_CONTAINERS => self::exportContainers(),
            self::IMPORT_CONTAINERS => self::importContainers(),
            self::COMPANIES => self::companies(),
            self::USERS => self::users(),
            self::HS_CODES => self::hsCodes(),
            default => throw new \InvalidArgumentException("Unknown export column key [{$key}]."),
        };
    }

    /**
     * A relation-collection column: the value is flattened into one cell from
     * the record's loaded relation, so it is never empty just because the
     * column name is not a real attribute (data_get cannot resolve HasMany).
     *
     * @param  callable(Model): string  $resolver
     */
    private static function relationColumn(string $name, string $heading, callable $resolver): Column
    {
        return Column::make($name)
            ->heading($heading)
            ->getStateUsing(fn ($record): string => $resolver($record));
    }

    /**
     * @param  array<int, string>  $names
     * @return array<int, Column>
     */
    private static function fields(array $names): array
    {
        return array_map(fn (string $name): Column => Column::make($name), $names);
    }

    /**
     * @return array<int, Column>
     */
    private static function exportShipments(): array
    {
        return [
            ...self::fields([
                'id', 'bl_number', 'shipment_mode', 'company_id', 'company_name_snapshot',
                'document_received_date', 'document_received_by',
                'aju_number', 'do_number', 'shipping_line', 'vessel_name', 'voyage_number',
                'port_of_loading', 'port_of_discharge', 'depot_closing_at', 'cy_closing_at',
                'pickup_depot_name', 'stuffing_date', 'stuffing_destination',
                'departure_date', 'eta_at',
                'status', 'current_milestone', 'completed_at',
                'created_at', 'updated_at', 'deleted_at',
            ]),
            Column::make('company.name')->heading('Company'),
            Column::make('documentReceivedBy.name')->heading('Document received by'),
            self::relationColumn('containers.container_number', 'Container numbers', fn ($record): string => $record->containers->pluck('container_number')->filter()->implode(', ')),
            self::relationColumn('containers.size', 'Container sizes', fn ($record): string => $record->containers->pluck('size')->filter()->implode(', ')),
        ];
    }

    /**
     * @return array<int, Column>
     */
    private static function importShipments(): array
    {
        return [
            ...self::fields([
                'id', 'bl_number', 'shipment_mode', 'company_id', 'company_name_snapshot',
                'document_received_date', 'document_received_by',
                'shipping_line', 'vessel_name', 'confirmation_checklist',
                'aju_number', 'voyage_number', 'billing_issuance_status',
                'port_of_loading', 'departure_date', 'port_of_discharge',
                'eta_at', 'billing_response', 'goods_description',
                'packages', 'terminal_name', 'loading_date', 'loading_destination',
                'status', 'current_milestone', 'completed_at',
                'created_at', 'updated_at', 'deleted_at',
            ]),
            Column::make('company.name')->heading('Company'),
            Column::make('documentReceivedBy.name')->heading('Document received by'),
            self::relationColumn('containers.container_number', 'Container numbers', fn ($record): string => $record->containers->pluck('container_number')->filter()->implode(', ')),
            self::relationColumn('containers.size', 'Container sizes', fn ($record): string => $record->containers->pluck('size')->filter()->implode(', ')),
            self::relationColumn('hsCodes.code', 'HS codes', fn ($record): string => $record->hsCodes->pluck('code')->implode(', ')),
        ];
    }

    /**
     * @return array<int, Column>
     */
    private static function exportContainers(): array
    {
        return [
            ...self::fields([
                'id', 'export_shipment_id', 'container_number', 'size', 'seal_number',
                'driver_name', 'license_number', 'driver_license_number',
                'tracking_position', 'tracking_position_url', 'stuffing_status',
                'port_of_loading', 'gate_in_cy_at', 'vgm_value',
                'final_checked', 'final_checked_at',
                'created_at', 'updated_at', 'deleted_at',
            ]),
            Column::make('shipment.bl_number')->heading('Shipment B/L number'),
            Column::make('shipment.company.name')->heading('Company'),
            self::relationColumn('shipment.status', 'Shipment status', fn ($record): string => (string) $record->shipment?->status?->label()),
            self::relationColumn('shipment.current_milestone', 'Shipment milestone', fn ($record): string => (string) $record->shipment?->current_milestone?->label()),
        ];
    }

    /**
     * @return array<int, Column>
     */
    private static function importContainers(): array
    {
        return [
            ...self::fields([
                'id', 'import_shipment_id', 'container_number', 'size', 'seal_number',
                'description_of_goods', 'packages',
                'driver_name', 'license_number', 'gate_out_cy_at',
                'tracking_position', 'tracking_position_url',
                'gross_weight', 'cbm',
                'factory_loading_status',
                'return_depot_name', 'empty_returned_at', 'status',
                'completed_at',
                'created_at', 'updated_at', 'deleted_at',
            ]),
            Column::make('shipment.bl_number')->heading('Shipment B/L number'),
            Column::make('shipment.company.name')->heading('Company'),
            self::relationColumn('shipment.status', 'Shipment status', fn ($record): string => (string) $record->shipment?->status?->label()),
            self::relationColumn('shipment.current_milestone', 'Shipment milestone', fn ($record): string => (string) $record->shipment?->current_milestone?->label()),
            self::relationColumn('hsCodes.code', 'HS codes', fn ($record): string => $record->hsCodes->pluck('code')->implode(', ')),
        ];
    }

    /**
     * @return array<int, Column>
     */
    private static function companies(): array
    {
        return [
            ...self::fields([
                'id', 'name', 'code', 'email', 'phone', 'address', 'is_active',
                'created_at', 'updated_at',
            ]),
            self::relationColumn('customers.email', 'Customer emails', fn ($record): string => $record->customers->pluck('email')->implode(', ')),
            self::relationColumn('operators.email', 'Operator emails', fn ($record): string => $record->operators->pluck('email')->implode(', ')),
            self::relationColumn('export_shipments_count', 'Export B/Ls', fn ($record): string => (string) $record->exportShipments()->count()),
            self::relationColumn('import_shipments_count', 'Import B/Ls', fn ($record): string => (string) $record->importShipments()->count()),
        ];
    }

    /**
     * @return array<int, Column>
     */
    private static function users(): array
    {
        return [
            ...self::fields([
                'id', 'name', 'email', 'phone', 'is_active', 'created_at', 'updated_at',
            ]),
            self::relationColumn('roles.name', 'Roles', fn ($record): string => $record->roles->pluck('name')->implode(', ')),
            self::relationColumn('companies.name', 'Companies', fn ($record): string => $record->companies->pluck('name')->implode(', ')),
        ];
    }

    /**
     * @return array<int, Column>
     */
    private static function hsCodes(): array
    {
        return [
            ...self::fields(['id', 'code', 'description', 'created_at', 'updated_at']),
            self::relationColumn('import_shipments_count', 'Import B/Ls', fn ($record): string => (string) $record->importShipments()->count()),
        ];
    }
}
