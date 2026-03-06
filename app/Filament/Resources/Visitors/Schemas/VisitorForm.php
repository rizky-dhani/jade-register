<?php

namespace App\Filament\Resources\Visitors\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VisitorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->required()
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->required()
                    ->maxLength(20),
                TextInput::make('affiliation')
                    ->maxLength(255),
                TextInput::make('profession')
                    ->required(),
                DatePicker::make('preferred_visit_date')
                    ->required(),
                TextInput::make('marketing_source'),
            ]);
    }
}
