<?php

namespace App\Filament\Resources\HandsOnRegistrations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HandsOnRegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('seminarRegistration.registration_code')
                    ->label('Registration Code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('seminarRegistration.name')
                    ->label('Participant')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('handsOn.name')
                    ->label('Hands On Event')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('handsOn.event_date')
                    ->label('Event Date')
                    ->date('F j, Y')
                    ->sortable(),

                TextColumn::make('registration_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'combined' => 'info',
                        'standalone' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'verified' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('verified_at')
                    ->label('Verified At')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('registration_type')
                    ->options([
                        'combined' => 'Combined',
                        'standalone' => 'Standalone',
                    ]),

                SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
