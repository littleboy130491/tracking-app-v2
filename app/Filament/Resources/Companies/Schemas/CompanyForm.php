<?php

/**
 * File: app/Filament/Resources/Companies/Schemas/CompanyForm.php
 * Responsibility: Admin form for companies.
 * What it does:
 * - Captures company identity/contact data and toggles access.
 * - The portal-users multi-select attaches/detaches existing users and offers
 *   an inline create form; created users get a random password and the
 *   customer role automatically (they sign in by one-time code only).
 * How to use: Rendered by CompanyResource create/edit pages.
 * How to extend: Add company fields here after adding the column + model fillable.
 */

namespace App\Filament\Resources\Companies\Schemas;

use App\Models\Role;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Company')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->helperText('Optional short code, must be unique.'),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(50),
                        Textarea::make('address')
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->default(true)
                            ->disabled(fn (): bool => ! auth()->user()?->hasAnyRole(Role::PRIVILEGED)),
                    ]),
                Section::make('Portal users')
                    ->schema([
                        Select::make('users')
                            ->label('Portal users')
                            ->relationship('users', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('Users who may see this company\'s shipments in the portal.')
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label('Email address')
                                    ->email()
                                    ->required()
                                    ->unique(User::class, 'email')
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(50),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $user = User::query()->create([
                                    ...$data,
                                    'password' => Str::password(),
                                    'is_active' => true,
                                ]);
                                $user->assignRole(Role::CUSTOMER);

                                return $user->getKey();
                            }),
                    ]),
            ]);
    }
}
