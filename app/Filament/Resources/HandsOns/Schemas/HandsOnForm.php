<?php

namespace App\Filament\Resources\HandsOns\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class HandsOnForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->rows(3)
                    ->maxLength(1000),

                DatePicker::make('event_date')
                    ->required()
                    ->label('Event Date')
                    ->displayFormat('F j, Y')
                    ->minDate('2026-11-13')
                    ->maxDate('2026-11-15'),

                TextInput::make('max_seats')
                    ->required()
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->default(30)
                    ->label('Maximum Seats'),

                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->default(0)
                    ->label('Price (IDR)'),

                TextInput::make('currency')
                    ->required()
                    ->default('IDR')
                    ->maxLength(3),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}
