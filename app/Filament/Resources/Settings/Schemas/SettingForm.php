<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label(__('filament.settings.form.key'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->alphaDash()
                    ->helperText(__('filament.settings.form.key_helper')),
                TextInput::make('value')
                    ->label(__('filament.settings.form.value'))
                    ->required(),
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
                Textarea::make('description')
                    ->label(__('filament.settings.form.description'))
                    ->columnSpanFull()
                    ->helperText(__('filament.settings.form.description_helper')),
            ]);
    }
}
