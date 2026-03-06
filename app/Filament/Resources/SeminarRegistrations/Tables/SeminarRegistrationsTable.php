<?php

namespace App\Filament\Resources\SeminarRegistrations\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SeminarRegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name_license')
                    ->label('Name on License')
                    ->searchable(),
                TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable(),
                TextColumn::make('npa')
                    ->label('NPA')
                    ->searchable(),
                TextColumn::make('pdgi_branch')
                    ->label('PDGI Branch')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
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
