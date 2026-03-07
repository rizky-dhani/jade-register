<?php

namespace App\Filament\Resources\Settings\Tables;

use App\Models\Setting;
use Filament\Tables\Table;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([])
            ->filters([])
            ->actions([])
            ->query(fn () => Setting::query()->whereNull('id'));
    }
}
