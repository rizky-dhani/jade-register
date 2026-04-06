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
                    ->label(__('filament.countries.country_code_iso')),
                TextInput::make('phone_code')
                    ->required()
                    ->maxLength(5)
                    ->label(__('filament.countries.phone_code')),
                Toggle::make('is_local')
                    ->label(__('filament.countries.mark_as_local'))
                    ->default(false),
            ]);
    }
}
