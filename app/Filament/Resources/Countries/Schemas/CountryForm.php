<?php

namespace App\Filament\Resources\Countries\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CountryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->required()
                    ->maxLength(2)
                    ->label('Country Code (ISO 2)'),
                TextInput::make('phone_code')
                    ->required()
                    ->maxLength(5)
                    ->label('Phone Code'),
                Toggle::make('is_local')
                    ->label('Mark as Local (Indonesia)')
                    ->default(false),
            ]);
    }
}
