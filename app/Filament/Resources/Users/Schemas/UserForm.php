<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('name_license')
                            ->label('Name License'),
                        TextInput::make('nik')
                            ->label('NIK'),
                        TextInput::make('pdgi_branch')
                            ->label('PDGI Branch'),
                        TextInput::make('kompetensi')
                            ->label('Kompetensi'),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required(),
                    ]),

                Section::make('Roles')
                    ->schema([
                        Select::make('roles')
                            ->relationship('roles', 'name')
                            ->options(
                                Role::orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->placeholder('Select roles...'),
                    ]),
            ]);
    }
}
