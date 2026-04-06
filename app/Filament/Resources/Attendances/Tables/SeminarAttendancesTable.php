<?php

namespace App\Filament\Resources\Attendances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SeminarAttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('participant_name')
                    ->label(__('filament.attendance.participant_name'))
                    ->state(function ($record): string {
                        if ($record->seminarRegistration) {
                            return $record->seminarRegistration->name;
                        }

                        return 'N/A';
                    })
                    ->searchable(query: function ($query, $search) {
                        $query->whereHas('seminarRegistration', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    }),
                IconColumn::make('is_checked_in')
                    ->label(__('filament.attendance.checked_in'))
                    ->state(fn ($record): bool => $record->checked_in_at !== null)
                    ->boolean(),
                TextColumn::make('checked_in_at')
                    ->label(__('filament.attendance.check_in_time'))
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
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('is_checked_in')
                    ->label(__('filament.attendance.check_in_status'))
                    ->options([
                        'checked_in' => __('filament.attendance.checked_in'),
                        'not_checked_in' => __('filament.attendance.not_checked_in'),
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
