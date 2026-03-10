<?php

namespace App\Filament\Resources\SeminarPackages\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SeminarPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Package Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Early Bird - Snack + Lunch'),

                        TextInput::make('code')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('e.g., local_early_bird_lunch')
                            ->helperText('Unique identifier used in the system. Use lowercase with underscores.'),

                        Textarea::make('description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->placeholder('Optional description of what this package includes'),
                    ]),

                Section::make('Pricing & Type')
                    ->schema([
                        Toggle::make('is_local')
                            ->label('Local Package (Indonesia)')
                            ->default(true)
                            ->live()
                            ->helperText('Toggle off for international packages'),

                        TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->label('Price Amount')
                            ->placeholder('e.g., 900000'),

                        TextInput::make('currency')
                            ->required()
                            ->default(fn ($get) => $get('is_local') ? 'IDR' : 'USD')
                            ->maxLength(3)
                            ->label('Currency')
                            ->placeholder('IDR or USD'),
                    ]),

                Section::make('Package Features')
                    ->schema([
                        Toggle::make('includes_lunch')
                            ->label('Includes Lunch')
                            ->default(false)
                            ->helperText('Check if this package includes lunch (not just snacks)'),

                        Toggle::make('is_early_bird')
                            ->label('Early Bird Pricing')
                            ->default(false)
                            ->helperText('Check if this is an early bird promotional price'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive packages will not be shown to users'),
                    ]),

                Section::make('Display Order')
                    ->schema([
                        TextInput::make('sort_order')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->label('Sort Order')
                            ->helperText('Lower numbers appear first. Use this to control display order.'),
                    ]),
            ]);
    }
}
