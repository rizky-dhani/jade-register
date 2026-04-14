<?php

namespace App\Filament\Resources\Addons\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
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
                            ->readOnly()
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

                        Repeater::make('disable_conditions')
                            ->label(__('filament.addons.disable_conditions'))
                            ->schema([
                                Select::make('model')
                                    ->label(__('filament.addons.condition_model'))
                                    ->required()
                                    ->options([
                                        'seminar' => __('filament.addons.model_seminar'),
                                        'addon' => __('filament.addons.model_addon'),
                                        'seminar_registration' => __('filament.addons.model_seminar_registration'),
                                    ])
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set) => $set('field', null)),

                                Select::make('field')
                                    ->label(__('filament.addons.condition_field'))
                                    ->required()
                                    ->options(function (Get $get): array {
                                        return match ($get('model')) {
                                            'seminar' => [
                                                'includes_lunch' => 'Includes Lunch',
                                                'is_active' => 'Is Active',
                                                'applies_to' => 'Applies To',
                                                'max_seats' => 'Max Seats',
                                            ],
                                            'addon' => [
                                                'is_active' => 'Is Active',
                                                'max_seats' => 'Max Seats',
                                                'code' => 'Code',
                                            ],
                                            'seminar_registration' => [
                                                'payment_status' => 'Payment Status',
                                            ],
                                            default => [],
                                        };
                                    })
                                    ->live(),

                                Select::make('operator')
                                    ->label(__('filament.addons.condition_operator'))
                                    ->required()
                                    ->options([
                                        '=' => __('filament.addons.operator_equals'),
                                        '!=' => __('filament.addons.operator_not_equals'),
                                        '>' => __('filament.addons.operator_greater_than'),
                                        '<' => __('filament.addons.operator_less_than'),
                                        '>=' => __('filament.addons.operator_greater_than_equal'),
                                        '<=' => __('filament.addons.operator_less_than_equal'),
                                        'is_null' => __('filament.addons.operator_is_null'),
                                        'is_not_null' => __('filament.addons.operator_is_not_null'),
                                        'contains' => __('filament.addons.operator_contains'),
                                        'not_contains' => __('filament.addons.operator_not_contains'),
                                    ]),

                                TextInput::make('value')
                                    ->label(__('filament.addons.condition_value'))
                                    ->required()
                                    ->helperText(__('filament.addons.condition_value_helper')),
                            ])
                            ->columns(4)
                            ->collapsible()
                            ->itemLabel(function (array $state): ?string {
                                if (empty($state['model']) || empty($state['field'])) {
                                    return null;
                                }

                                return "{$state['model']}.{$state['field']} {$state['operator']} {$state['value']}";
                            })
                            ->addActionLabel(__('filament.addons.disable_conditions_add'))
                            ->helperText(__('filament.addons.disable_conditions_helper'))
                            ->columnSpanFull(),

                        Select::make('disable_condition')
                            ->label(__('filament.addons.disable_condition'))
                            ->default('when_full')
                            ->options([
                                'never' => __('filament.addons.disable_condition_never'),
                                'when_full' => __('filament.addons.disable_condition_when_full'),
                                'when_date_passed' => __('filament.addons.disable_condition_when_date_passed'),
                                'always' => __('filament.addons.disable_condition_always'),
                            ])
                            ->helperText(__('filament.addons.disable_condition_helper'))
                            ->columnSpanFull(),

                        DatePicker::make('available_from')
                            ->label(__('filament.addons.available_from'))
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->helperText(__('filament.addons.available_from_helper')),

                        DatePicker::make('available_until')
                            ->label(__('filament.addons.available_until'))
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->helperText(__('filament.addons.available_until_helper')),

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
