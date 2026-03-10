<?php

namespace App\Filament\Resources\HandsOns\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HandsOnsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('event_date')
                    ->date('F j, Y')
                    ->sortable(),

                TextColumn::make('max_seats')
                    ->label('Max Seats')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('registrations_count')
                    ->label('Registered')
                    ->counts('handsOnRegistrations')
                    ->numeric(),

                TextColumn::make('available_seats')
                    ->label('Available')
                    ->state(function ($record) {
                        $registered = $record->handsOnRegistrations()
                            ->whereIn('payment_status', ['pending', 'verified'])
                            ->count();

                        return max(0, $record->max_seats - $registered);
                    })
                    ->numeric()
                    ->color(function ($record) {
                        $registered = $record->handsOnRegistrations()
                            ->whereIn('payment_status', ['pending', 'verified'])
                            ->count();
                        $available = max(0, $record->max_seats - $registered);
                        if ($available === 0) {
                            return 'danger';
                        }
                        if ($available <= 5) {
                            return 'warning';
                        }

                        return 'success';
                    }),

                TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->filters([
                SelectFilter::make('event_date')
                    ->options([
                        '2026-11-13' => 'November 13, 2026',
                        '2026-11-14' => 'November 14, 2026',
                        '2026-11-15' => 'November 15, 2026',
                    ]),

                Filter::make('is_active')
                    ->label('Active Only')
                    ->query(fn (Builder $query) => $query->where('is_active', true)),

                Filter::make('has_available_seats')
                    ->label('Has Available Seats')
                    ->query(function (Builder $query) {
                        $query->whereHas('handsOnRegistrations', function ($q) {
                            $q->whereIn('payment_status', ['pending', 'verified']);
                        }, '<', $query->raw('max_seats'));
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('event_date');
    }
}
