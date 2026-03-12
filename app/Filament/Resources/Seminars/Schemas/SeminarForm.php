<?php

namespace App\Filament\Resources\Seminars\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SeminarForm
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
                            ->placeholder('e.g., Early Bird - Snack + Lunch')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $state, callable $set, \Filament\Schemas\Components\Utilities\Get $get) {
                                // Only auto-generate code if record doesn't exist yet (create page)
                                if ($get('id') === null) {
                                    $set('code', \Illuminate\Support\Str::slug($state, '_'));
                                }
                            }),

                        TextInput::make('code')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('e.g., local_early_bird_lunch')
                            ->helperText('Auto-generated from package name on create. Editable on edit page.')
                            ->readOnly(fn (\Filament\Schemas\Components\Utilities\Get $get): bool => $get('id') === null),

                        Textarea::make('description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->placeholder('Optional description of what this package includes'),
                    ]),

                Section::make('Pricing & Type')
                    ->columns(3)
                    ->schema([
                        Select::make('applies_to')
                            ->label('Applies To')
                            ->options([
                                'local' => 'Local (Indonesia)',
                                'international' => 'International',
                                'all' => 'All Participants',
                            ])
                            ->default('local')
                            ->required()
                            ->live()
                            ->helperText('Select which participant type this package applies to'),

                        // TextInput::make('amount')
                        //     ->required()
                        //     ->numeric()
                        //     ->integer()
                        //     ->minValue(0)
                        //     ->label('Price Amount (Legacy)')
                        //     ->placeholder('e.g., 900000')
                        //     ->helperText('Legacy field - use Original/Discounted Price below for new pricing'),

                        TextInput::make('original_price')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->label('Original Price')
                            ->placeholder('e.g., 1000000')
                            ->helperText('Regular price before any discounts'),

                        TextInput::make('discounted_price')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->label('Discounted Price (Early Bird)')
                            ->placeholder('e.g., 900000')
                            ->helperText('Early bird promotional price (leave empty for no discount)'),

                        TextInput::make('stock_limit')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->label('Stock Limit')
                            ->placeholder('e.g., 100')
                            ->helperText('Maximum number of registrations allowed (leave empty for unlimited)'),

                        TextInput::make('early_bird_deadline')
                            ->label('Early Bird Deadline')
                            ->type('datetime-local')
                            ->helperText('Deadline for early bird pricing (leave empty to use is_early_bird toggle only)'),

                        Select::make('currency')
                            ->required()
                            ->default(fn (\Filament\Schemas\Components\Utilities\Get $get): string => $get('applies_to') === 'local' ? 'IDR' : 'USD')
                            ->options([
                                'IDR' => 'IDR - Indonesian Rupiah',
                                'USD' => 'USD - US Dollar',
                            ])
                            ->label('Currency'),
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
