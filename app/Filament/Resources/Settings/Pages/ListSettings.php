<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Setting'),
                TextColumn::make('value')
                    ->label('Value'),
            ])
            ->recordUrl(fn ($record) => SettingResource::getUrl('edit', ['record' => $record->id]));
    }
}
