<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Permission;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Role Information')
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('guard_name')
                            ->required(),
                    ]),

                Section::make('Permissions')
                    ->schema([
                        Select::make('permissions')
                            ->relationship('permissions', 'name')
                            ->options(
                                Permission::orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->placeholder('Select permissions...'),
                    ]),
            ]);
    }
}
