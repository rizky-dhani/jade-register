<?php

namespace App\Filament\Resources\Visitors\Tables;

use Filament\Tables\Columns\DateColumn;
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
                DateColumn::make('preferred_visit_date')
                    ->sortable()
                    ->format('d F Y'),
                TextColumn::make('marketing_source')
                    ->searchable(),
                DateColumn::make('created_at')
                    ->sortable()
                    ->format('d M Y H:i'),
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
