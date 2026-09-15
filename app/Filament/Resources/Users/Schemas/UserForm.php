<?php

/**
 * File: app/Filament/Resources/Users/Schemas/UserForm.php
 * Responsibility: Admin form for staff and customer-portal users.
 * What it does:
 * - Assigns roles and the companies a portal user may handle; passwords are
 *   optional because portal users sign in with OTP.
 * - New users default to the customer role, and the roles offered are limited
 *   to what the signed-in user may hand out (App\Support\Authorization\AssignableRoles).
 * How to use: Rendered by UserResource create/edit pages.
 * How to extend: Add profile fields after adding the column + model fillable.
 */

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Role;
use App\Support\Authorization\AssignableRoles;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(50),
                        TextInput::make('password')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->default(fn (string $operation): ?string => $operation === 'create' ? Str::password() : null)
                            ->helperText('Customer users sign in with a one-time password; a password is only needed for staff.'),
                        Toggle::make('is_active')
                            ->default(true)
                            ->disabled(fn (): bool => ! auth()->user()?->hasAnyRole(Role::PRIVILEGED)),
                    ]),
                Section::make('Access')
                    ->schema([
                        Select::make('roles')
                            ->label('Roles')
                            ->relationship(
                                name: 'roles',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->when(
                                    ! AssignableRoles::isSuperAdmin(auth()->user()),
                                    fn (Builder $inner): Builder => $inner->whereNotIn('roles.name', Role::PRIVILEGED),
                                ),
                            )
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->default(fn (): array => self::defaultRoleIds()),
                        Select::make('companies')
                            ->relationship('companies', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ]),
            ]);
    }

    /**
     * A new user is a portal customer unless told otherwise.
     *
     * @return array<int, int>
     */
    private static function defaultRoleIds(): array
    {
        $customerId = Role::query()
            ->where('name', Role::CUSTOMER)
            ->where('guard_name', 'web')
            ->value('id');

        return $customerId === null ? [] : [(int) $customerId];
    }
}
