<?php

namespace App\Filament\Resources\Attendances\Tables;

use App\Models\Attendance;
use App\Services\AttendanceCheckOutService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
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
                            return $record->seminarRegistration->name_license;
                        }

                        return 'N/A';
                    })
                    ->searchable(query: function ($query, $search) {
                        $query->whereHas('seminarRegistration', function ($q) use ($search) {
                            $q->where('name_license', 'like', "%{$search}%");
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
                IconColumn::make('is_checked_out')
                    ->label(__('filament.attendance.checked_out'))
                    ->state(fn ($record): bool => $record->checked_out_at !== null)
                    ->boolean(),
                TextColumn::make('checked_out_at')
                    ->label(__('filament.attendance.check_out_time'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('checkedOutBy.name')
                    ->label(__('filament.attendance.checked_out_by'))
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
                SelectFilter::make('is_checked_out')
                    ->label(__('filament.attendance.check_out_status'))
                    ->options([
                        'checked_out' => __('filament.attendance.checked_out'),
                        'not_checked_out' => __('filament.attendance.not_checked_out'),
                    ])
                    ->query(function ($query, array $data): void {
                        if ($data['value'] === 'checked_out') {
                            $query->whereNotNull('checked_out_at');
                        } elseif ($data['value'] === 'not_checked_out') {
                            $query->whereNull('checked_out_at');
                        }
                    }),
            ])
            ->recordActions([
                Action::make('checkOut')
                    ->label(__('filament.attendance.check_out'))
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading(__('filament.attendance.check_out'))
                    ->modalDescription(fn (Attendance $record): string => __('filament.attendance.check_out_confirm', [
                        'name' => $record->seminarRegistration?->name_license ?? __('filament.attendance.participant_name'),
                    ]))
                    ->modalSubmitActionLabel(__('filament.attendance.check_out'))
                    // Gated on the desk role, not on `update attendances`: the Admin
                    // role is never granted that permission, and the same people check
                    // participants out on the verify screen (AttendanceVerify::canCheckIn).
                    ->visible(fn (Attendance $record): bool => $record->checked_in_at !== null
                        && $record->checked_out_at === null
                        && (auth()->user()?->hasRole(['Admin', 'Super Admin']) ?? false))
                    ->action(function (Attendance $record): void {
                        $staff = auth()->user();

                        if (! $staff) {
                            return;
                        }

                        app(AttendanceCheckOutService::class)->checkOut($record, $staff);

                        Notification::make()->success()->title(__('filament.attendance.checked_out'))->send();
                    }),
                EditAction::make()
                    ->visible(fn (): bool => auth()->user()?->can('update attendances') ?? false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->can('delete attendances') ?? false),
                ]),
            ]);
    }
}
