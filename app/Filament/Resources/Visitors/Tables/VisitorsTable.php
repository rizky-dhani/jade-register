<?php

namespace App\Filament\Resources\Visitors\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VisitorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('profession')
                    ->searchable(),
                TextColumn::make('preferred_visit_date')
                    ->date('d F Y')
                    ->sortable(),
                TextColumn::make('marketing_source')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->sortable()
                    ->date('d F Y'),
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
