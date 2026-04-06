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
                            ->label(__('filament.seminars.applies_to'))
                            ->options([
                                'local' => 'Local (Indonesia)',
                                'international' => 'International',
                                'all' => 'All Participants',
                            ])
                            ->default('local')
                            ->required()
                            ->live()
                            ->helperText('Select which participant type this package applies to'),

                        TextInput::make('original_price')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->label(__('filament.seminars.original_price'))
                            ->placeholder('e.g., 1000000')
                            ->helperText('Regular price before any discounts'),

                        TextInput::make('discounted_price')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->label(__('filament.seminars.discounted_price'))
                            ->placeholder('e.g., 900000')
                            ->helperText('Early bird promotional price (leave empty for no discount)'),

                        TextInput::make('max_seats')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->label(__('filament.seminars.max_seats'))
                            ->placeholder('e.g., 100')
                            ->helperText('Maximum number of registrations allowed (leave empty for unlimited)'),

                        TextInput::make('early_bird_deadline')
                            ->label(__('filament.hands_on.early_bird_deadline'))
                            ->type('datetime-local')
                            ->helperText('Deadline for early bird pricing (leave empty to use is_early_bird toggle only)'),

                        Select::make('currency')
                            ->required()
                            ->default(fn (\Filament\Schemas\Components\Utilities\Get $get): string => $get('applies_to') === 'local' ? 'IDR' : 'USD')
                            ->options([
                                'IDR' => 'IDR - Indonesian Rupiah',
                                'USD' => 'USD - US Dollar',
                            ])
                            ->label(__('filament.hands_on.currency')),
                    ]),

                Section::make('Package Features')
                    ->schema([
                        Toggle::make('includes_lunch')
                            ->label(__('filament.seminars.includes_lunch'))
                            ->default(false)
                            ->helperText('Check if this package includes lunch (not just snacks)'),

                        Toggle::make('is_early_bird')
                            ->label(__('filament.seminars.early_bird'))
                            ->default(false)
                            ->helperText('Check if this is an early bird promotional price'),

                        Toggle::make('is_active')
                            ->label(__('filament.seminars.active'))
                            ->default(true)
                            ->helperText('Inactive packages will not be shown to users'),
                    ]),

                Section::make('Display Order')
                    ->schema([
                        TextInput::make('sort_order')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->label(__('filament.hands_on.sort_order'))
                            ->helperText('Lower numbers appear first. Use this to control display order.'),
                    ]),
            ]);
    }
}
