<?php

namespace App\Filament\Resources\Attendances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HandsOnAttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('participant_name')
                    ->label('Participant Name')
                    ->state(function ($record): string {
                        if ($record->handsOnRegistration && $record->handsOnRegistration->seminarRegistration) {
                            return $record->handsOnRegistration->seminarRegistration->name;
                        }

                        return 'N/A';
                    })
                    ->searchable(query: function ($query, $search) {
                        $query->whereHas('handsOnRegistration.seminarRegistration', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('hands_on_name')
                    ->label('Hands On Session')
                    ->state(function ($record): string {
                        if ($record->handsOnRegistration && $record->handsOnRegistration->handsOn) {
                            return $record->handsOnRegistration->handsOn->name;
                        }

                        return 'N/A';
                    })
                    ->searchable(),
                IconColumn::make('is_checked_in')
                    ->label('Checked In')
                    ->state(fn ($record): bool => $record->checked_in_at !== null)
                    ->boolean(),
                TextColumn::make('checked_in_at')
                    ->label('Check-in Time')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('checkedInBy.name')
                    ->label('Checked In By')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('is_checked_in')
                    ->label('Check-in Status')
                    ->options([
                        'checked_in' => 'Checked In',
                        'not_checked_in' => 'Not Checked In',
                    ])
                    ->query(function ($query, array $data): void {
                        if ($data['value'] === 'checked_in') {
                            $query->whereNotNull('checked_in_at');
                        } elseif ($data['value'] === 'not_checked_in') {
                            $query->whereNull('checked_in_at');
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
