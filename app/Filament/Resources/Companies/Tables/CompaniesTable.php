<?php

/**
 * File: app/Filament/Resources/Companies/Tables/CompaniesTable.php
 * Responsibility: Admin list of companies.
 * What it does:
 * - Shows contact data plus the portal users linked to the company.
 * How to use: Rendered by ListCompanies.
 * How to extend: Add columns/filters as the company data grows.
 */

namespace App\Filament\Resources\Companies\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\Company;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                TextColumn::make('users.email')
                    ->label('Portal users')
                    ->listWithLineBreaks()
                    ->url(function (string $state, Company $record): ?string {
                        $user = $record->users->firstWhere('email', $state);

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
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
