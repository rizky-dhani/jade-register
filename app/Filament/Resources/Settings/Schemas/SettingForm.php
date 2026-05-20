<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament.settings.section.basic'))
                    ->description(__('filament.settings.section.basic_description'))
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        TextInput::make('label')
                            ->label(__('filament.settings.form.label'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                        TextInput::make('key')
                            ->label(__('filament.settings.form.key'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->helperText(__('filament.settings.form.key_helper'))
                            ->disabled(fn (?string $operation): bool => $operation === 'edit')
                            ->columnSpan(1),
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
                            ->helperText(__('filament.settings.form.type_helper'))
                            ->disabled(fn (?string $operation): bool => $operation === 'edit')
                            ->columnSpanFull(),
                    ]),
                Section::make(__('filament.settings.section.value'))
                    ->description(__('filament.settings.section.value_description'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsible()
                    ->schema([
                        TextInput::make('value')
                            ->label(__('filament.settings.form.value'))
                            ->required()
                            ->maxLength(65535)
                            ->visible(fn (Get $get): bool => $get('type') === 'string'),
                        TextInput::make('value')
                            ->label(__('filament.settings.form.value'))
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(999999999)
                            ->visible(fn (Get $get): bool => $get('type') === 'integer'),
                        TextInput::make('value')
                            ->label(__('filament.settings.form.value'))
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->visible(fn (Get $get): bool => $get('type') === 'float'),
                        Toggle::make('value')
                            ->label(__('filament.settings.form.value'))
                            ->visible(fn (Get $get): bool => $get('type') === 'boolean'),
                        Textarea::make('value')
                            ->label(__('filament.settings.form.value'))
                            ->required()
                            ->json()
                            ->maxLength(65535)
                            ->helperText(__('filament.settings.form.json_helper'))
                            ->visible(fn (Get $get): bool => $get('type') === 'array'),
                    ]),
                Section::make(__('filament.settings.section.notes'))
                    ->description(__('filament.settings.section.notes_description'))
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->schema([
                        Textarea::make('description')
                            ->label(__('filament.settings.form.description'))
                            ->maxLength(65535)
                            ->helperText(__('filament.settings.form.description_helper')),
                    ]),
            ]);
    }
}
