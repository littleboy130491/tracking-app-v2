<?php

/**
 * File: app/Filament/Resources/Users/Tables/UsersTable.php
 * Responsibility: Admin list of users.
 * What it does:
 * - Shows roles, linked company names (each linking to its company edit
 *   page) and active state; filters by role and company.
 * - Offers an Impersonate row action (visible only to admin/super_admin;
 *   enforced by User::canImpersonate()/canBeImpersonated()).
 * How to use: Rendered by ListUsers.
 * How to extend: Add filters/columns as user administration grows.
 */

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Companies\CompanyResource;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use STS\FilamentImpersonate\Actions\Impersonate;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('roles.name')
                    ->badge()
                    ->separator(','),
                TextColumn::make('companies.name')
                    ->label('Companies')
                    ->listWithLineBreaks()
                    ->placeholder('—')
                    ->url(function (string $state, User $record): ?string {
                        $company = $record->companies->firstWhere('name', $state);

                        return $company === null ? null : CompanyResource::getUrl('edit', ['record' => $company]);
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
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                SelectFilter::make('companies')
                    ->relationship('companies', 'name')
                    ->multiple()
                    ->preload(),
                TernaryFilter::make('is_active'),
            ])
            ->recordActions([
                Impersonate::make()->redirectTo('/'),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
