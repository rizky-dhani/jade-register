<?php

namespace App\Filament\Resources\Attendances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('participant_name')
                    ->label('Participant Name')
                    ->state(function ($record): string {
                        if ($record->isSeminar() && $record->seminarRegistration) {
                            return $record->seminarRegistration->name;
                        }

                        if ($record->isHandsOn() && $record->handsOnRegistration && $record->handsOnRegistration->seminarRegistration) {
                            return $record->handsOnRegistration->seminarRegistration->name;
                        }

                        return 'N/A';
                    }),
                IconColumn::make('is_seminar_checked_in')
                    ->label('Seminar Registration')
                    ->state(fn ($record): bool => $record->isSeminar() && $record->checked_in_at !== null)
                    ->boolean(),
                TextColumn::make('hands_on_registrations')
                    ->label('Hands On Registrations')
                    ->state(function ($record): string {
                        $handsOns = collect();

                        if ($record->isSeminar() && $record->seminarRegistration) {
                            $handsOns = $record->seminarRegistration->handsOnRegistrations;
                        } elseif ($record->isHandsOn() && $record->handsOnRegistration) {
                            $participantSeminar = $record->handsOnRegistration->seminarRegistration;
                            if ($participantSeminar) {
                                $handsOns = $participantSeminar->handsOnRegistrations;
                            }
                        }

                        if ($handsOns->isEmpty()) {
                            return 'N/A';
                        }

                        return $handsOns
                            ->load('handsOn')
                            ->pluck('handsOn.name')
                            ->join(', ');
                    }),
                TextColumn::make('activity_type')
                    ->label('Activity Type')
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->searchable(),
                TextColumn::make('checked_in_at')
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
            ->filters([
                //
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
