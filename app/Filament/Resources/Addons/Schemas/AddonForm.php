<?php

namespace App\Filament\Resources\Addons\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AddonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Section: Information
                Section::make(__('filament.addons.section_information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('filament.addons.name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('code', Str::upper(Str::snake($state))))
                            ->columnSpanFull(),

                        TextInput::make('code')
                            ->label(__('filament.addons.code'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText(__('filament.addons.code_helper'))
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label(__('filament.addons.description'))
                            ->columnSpanFull()
                            ->rows(3),
                    ])->columns(2),

                // Section: Pricing
                Section::make(__('filament.addons.section_pricing'))
                    ->schema([
                        TextInput::make('price')
                            ->label(__('filament.addons.price'))
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->prefix(fn (Get $get): string => $get('currency') === 'USD' ? '$' : 'Rp')
                            ->live(),

                        Select::make('currency')
                            ->label(__('filament.addons.currency'))
                            ->required()
                            ->default('IDR')
                            ->options([
                                'IDR' => 'IDR (Indonesian Rupiah)',
                                'USD' => 'USD (US Dollar)',
                            ])
                            ->live(),
                    ])->columns(2),

                // Section: Availability
                Section::make(__('filament.addons.section_availability'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('filament.addons.is_active'))
                            ->default(true)
                            ->columnSpanFull(),

                        DatePicker::make('available_from')
                            ->label(__('filament.addons.available_from'))
                            ->native(false)
                            ->displayFormat('d M Y'),

                        DatePicker::make('available_until')
                            ->label(__('filament.addons.available_until'))
                            ->native(false)
                            ->displayFormat('d M Y'),

                        TextInput::make('max_seats')
                            ->label(__('filament.addons.max_seats'))
                            ->nullable()
                            ->numeric()
                            ->minValue(1)
                            ->helperText(__('filament.addons.max_seats_helper')),

                        TextInput::make('sort_order')
                            ->label(__('filament.addons.sort_order'))
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])->columns(2),
            ]);
    }
}
