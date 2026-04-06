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
                            ->label(__('filament.users.name_license')),
                        TextInput::make('nik')
                            ->label(__('filament.users.nik')),
                        TextInput::make('pdgi_branch')
                            ->label(__('filament.users.pdgi_branch')),
                        TextInput::make('kompetensi')
                            ->label(__('filament.users.kompetensi')),
                        TextInput::make('email')
                            ->label(__('filament.users.email'))
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
