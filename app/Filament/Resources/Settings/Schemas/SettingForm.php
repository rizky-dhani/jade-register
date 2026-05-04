<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        TextInput::make('key')
                            ->label(__('filament.settings.form.key'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->helperText(__('filament.settings.form.key_helper')),
                        Select::make('type')
                            ->label(__('filament.settings.form.type'))
                            ->options([
                                'string' => 'String',
                                'integer' => 'Integer',
                                'float' => 'Float',
                                'boolean' => 'Boolean',
                                'array' => 'Array (JSON)',
                            ])
                            ->required()
                            ->default('string')
                            ->live()
                            ->helperText(__('filament.settings.form.type_helper')),
                    ]),
                TextInput::make('value')
                    ->label(__('filament.settings.form.value'))
                    ->required()
                    ->visible(fn (Get $get): bool => $get('type') === 'string'),
                TextInput::make('value')
                    ->label(__('filament.settings.form.value'))
                    ->required()
                    ->numeric()
                    ->visible(fn (Get $get): bool => $get('type') === 'integer'),
                TextInput::make('value')
                    ->label(__('filament.settings.form.value'))
                    ->required()
                    ->numeric()
                    ->step(0.01)
                    ->visible(fn (Get $get): bool => $get('type') === 'float'),
                Toggle::make('value')
                    ->label(__('filament.settings.form.value'))
                    ->required()
                    ->visible(fn (Get $get): bool => $get('type') === 'boolean'),
                Textarea::make('value')
                    ->label(__('filament.settings.form.value'))
                    ->required()
                    ->json()
                    ->visible(fn (Get $get): bool => $get('type') === 'array'),
                Textarea::make('description')
                    ->label(__('filament.settings.form.description'))
                    ->helperText(__('filament.settings.form.description_helper')),
            ]);
    }
}
