<?php

/**
 * File: app/Filament/Resources/Companies/Tables/CompaniesTable.php
 * Responsibility: Admin list of companies.
 * What it does:
 * - Shows contact data plus the linked users, split by role: customer
 *   (portal access) and operator (assigned staff).
 * - Offers a CSV header action, restricted to admin/super_admin via
 *   User::canExportTables().
 * - Offers a "Prune old data" header action for the same roles, deleting
 *   companies older than the retention window.
 * How to use: Rendered by ListCompanies.
 * How to extend: Add columns/filters as the company data grows.
 */

namespace App\Filament\Resources\Companies\Tables;

use App\Filament\Concerns\PrunableTableHeaderAction;
use App\Filament\Concerns\TableExportColumns;
use App\Filament\Resources\Users\UserResource;
use App\Models\Company;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['customers', 'operators']))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('customers.email')
                    ->label('Customers')
                    ->listWithLineBreaks()
                    ->placeholder('—')
                    ->url(function (string $state, Company $record): ?string {
                        $user = $record->customers->firstWhere('email', $state);

                        return $user === null ? null : UserResource::getUrl('edit', ['record' => $user]);
                    }),
                TextColumn::make('operators.email')
                    ->label('Operators')
                    ->listWithLineBreaks()
                    ->placeholder('—')
                    ->url(function (string $state, Company $record): ?string {
                        $user = $record->operators->firstWhere('email', $state);

                        return $user === null ? null : UserResource::getUrl('edit', ['record' => $user]);
                    }),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                TernaryFilter::make('is_active'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        ExcelExport::make()->withColumns(TableExportColumns::for(TableExportColumns::COMPANIES)),
                    ])
                    ->visible(fn (): bool => (bool) auth()->user()?->canExportTables()),
                PrunableTableHeaderAction::make('companies'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make()
                        ->visible(fn (): bool => (bool) auth()->user()?->can('ForceDeleteAny:Company')),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
