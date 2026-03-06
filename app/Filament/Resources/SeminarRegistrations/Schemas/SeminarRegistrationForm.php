<?php

namespace App\Filament\Resources\SeminarRegistrations\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SeminarRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->required()
                    ->email()
                    ->maxLength(255),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('name_license')
                    ->label('Name on License')
                    ->required()
                    ->maxLength(255),
                TextInput::make('nik')
                    ->label('NIK')
                    ->required()
                    ->maxLength(20),
                TextInput::make('npa')
                    ->label('NPA')
                    ->required()
                    ->maxLength(20),
                TextInput::make('pdgi_branch')
                    ->label('PDGI Branch')
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->required()
                    ->maxLength(20),
            ]);
    }
}
