<?php

namespace App\Filament\Resources\Settings\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Setting Key')
                    ->searchable(),
                TextColumn::make('value')
                    ->label('Value')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                //
            ]);
    }
}
