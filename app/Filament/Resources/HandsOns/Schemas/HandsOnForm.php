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
                    ->label(__('filament.hands_on.event_date'))
                    ->displayFormat('F j, Y')
                    ->minDate('2026-11-13')
                    ->maxDate('2026-11-15'),

                TextInput::make('max_seats')
                    ->required()
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->default(30)
                    ->label(__('filament.hands_on.max_seats_legacy'))
                    ->helperText('Legacy field - use Stock Limit below for new stock management'),

                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->default(0)
                    ->label(__('filament.hands_on.price_legacy'))
                    ->helperText('Legacy field - use Original/Discounted Price below for new pricing'),

                TextInput::make('original_price')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->label(__('filament.hands_on.original_price'))
                    ->placeholder('e.g., 1500000')
                    ->helperText('Regular price before any discounts'),

                TextInput::make('discounted_price')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->label(__('filament.hands_on.discounted_price'))
                    ->placeholder('e.g., 1200000')
                    ->helperText('Early bird promotional price (leave empty for no discount)'),

                TextInput::make('max_seats')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->label(__('filament.hands_on.max_seats'))
                    ->placeholder('e.g., 30')
                    ->helperText('Maximum number of registrations allowed (leave empty for unlimited)'),

                TextInput::make('early_bird_deadline')
                    ->label(__('filament.hands_on.early_bird_deadline'))
                    ->type('datetime-local')
                    ->helperText('Deadline for early bird pricing (leave empty for no deadline)'),

                TextInput::make('currency')
                    ->required()
                    ->default('IDR')
                    ->maxLength(3),

                Toggle::make('is_active')
                    ->label(__('filament.hands_on.active'))
                    ->default(true),
            ]);
    }
}
