<?php

/**
 * File: app/Filament/Resources/HsCodes/Schemas/HsCodeForm.php
 * Responsibility: Admin form for an HS code.
 * What it does:
 * - The code is unique master data; the description is the official wording
 *   reused on every shipment that attaches it.
 * How to use: Rendered by the HS-code create/edit pages and by the B/L
 *   form's inline create option.
 * How to extend: Add columns to `hs_codes` and a matching field here.
 */

namespace App\Filament\Resources\HsCodes\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HsCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('code')
                            ->label('HS code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(30),
                        Textarea::make('description')
                            ->rows(3),
                    ]),
            ]);
    }
}
