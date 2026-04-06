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
                    ->label(__('filament.attendance.participant_name'))
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
                    ->label(__('filament.attendance.seminar_registration'))
                    ->state(fn ($record): bool => $record->isSeminar() && $record->checked_in_at !== null)
                    ->boolean(),
                TextColumn::make('hands_on_registrations')
                    ->label(__('filament.attendance.hands_on_registrations'))
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
                    ->label(__('filament.attendance.activity_type'))
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->searchable(),
                TextColumn::make('checked_in_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('checkedInBy.name')
                    ->label(__('filament.attendance.checked_in_by'))
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
