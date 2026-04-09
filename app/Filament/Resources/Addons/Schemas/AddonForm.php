<?php

namespace App\Filament\Resources\Addons\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AddonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label(__('filament.addons.name'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('code')
                    ->label(__('filament.addons.code'))
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText(__('filament.addons.code_helper')),

                Textarea::make('description')
                    ->label(__('filament.addons.description'))
                    ->columnSpanFull()
                    ->rows(3),

                TextInput::make('price')
                    ->label(__('filament.addons.price'))
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->prefix('Rp'),

                TextInput::make('currency')
                    ->label(__('filament.addons.currency'))
                    ->required()
                    ->default('IDR')
                    ->maxLength(10),

                TextInput::make('max_seats')
                    ->label(__('filament.addons.max_seats'))
                    ->nullable()
                    ->numeric()
                    ->minValue(1)
                    ->helperText(__('filament.addons.max_seats_helper')),

                Toggle::make('is_active')
                    ->label(__('filament.addons.is_active'))
                    ->default(true)
                    ->columnSpanFull(),

                DatePicker::make('available_from')
                    ->label(__('filament.addons.available_from'))
                    ->native(false),

                DatePicker::make('available_until')
                    ->label(__('filament.addons.available_until'))
                    ->native(false),

                TextInput::make('sort_order')
                    ->label(__('filament.addons.sort_order'))
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }
}
