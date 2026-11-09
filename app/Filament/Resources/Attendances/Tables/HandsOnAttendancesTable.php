<?php

namespace App\Filament\Resources\Attendances\Tables;

use App\Models\Attendance;
use App\Models\HandsOnRegistration;
use App\Services\AttendanceCheckInService;
use App\Services\AttendanceCheckOutService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * The hands-on desk roster: one row per verified session registration, whether
 * or not it has been scanned, with the attendance row joined in when it exists.
 */
class HandsOnAttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('participant_name')
                    ->label(__('filament.attendance.participant_name'))
                    ->state(fn (HandsOnRegistration $record): string => self::participantName($record))
                    // participant_name is computed state, not a column: a plain
                    // searchable() would query hands_on_registrations.participant_name.
                    ->searchable(query: function ($query, $search) {
                        $query->where(function ($inner) use ($search): void {
                            $inner->where('name_license', 'like', "%{$search}%")
                                ->orWhereHas('seminarRegistration', function ($seminar) use ($search): void {
                                    $seminar->where('name_license', 'like', "%{$search}%");
                                });
                        });
                    }),
                TextColumn::make('registration_code')
                    ->label(__('filament.attendance.registration_code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('handsOn.name')
                    ->label(__('filament.attendance.hands_on_session'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_checked_in')
                    ->label(__('filament.attendance.checked_in'))
                    ->state(fn (HandsOnRegistration $record): bool => $record->attendance?->checked_in_at !== null)
                    ->boolean(),
                TextColumn::make('attendance.checked_in_at')
                    ->label(__('filament.attendance.check_in_time'))
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('attendance.checkedInBy.name')
                    ->label(__('filament.attendance.checked_in_by'))
                    ->placeholder('-'),
                IconColumn::make('is_checked_out')
                    ->label(__('filament.attendance.checked_out'))
                    ->state(fn (HandsOnRegistration $record): bool => $record->attendance?->checked_out_at !== null)
                    ->boolean(),
                TextColumn::make('attendance.checked_out_at')
                    ->label(__('filament.attendance.check_out_time'))
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('attendance.checkedOutBy.name')
                    ->label(__('filament.attendance.checked_out_by'))
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('registration_code', 'asc')
            // The record is a registration, not an attendance, so Filament's
            // default record link would point the attendance edit page at a
            // hands-on registration id.
            ->recordUrl(null)
            ->filters([
                SelectFilter::make('is_checked_in')
                    ->label(__('filament.attendance.check_in_status'))
                    ->options([
                        'checked_in' => __('filament.attendance.checked_in'),
                        'not_checked_in' => __('filament.attendance.not_checked_in'),
                    ])
                    ->query(function ($query, array $data): void {
                        if ($data['value'] === 'checked_in') {
                            $query->whereHas('attendance', fn ($attendance) => $attendance->whereNotNull('checked_in_at'));
                        } elseif ($data['value'] === 'not_checked_in') {
                            $query->whereDoesntHave('attendance', fn ($attendance) => $attendance->whereNotNull('checked_in_at'));
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
                            $query->whereHas('attendance', fn ($attendance) => $attendance->whereNotNull('checked_out_at'));
                        } elseif ($data['value'] === 'not_checked_out') {
                            $query->whereDoesntHave('attendance', fn ($attendance) => $attendance->whereNotNull('checked_out_at'));
                        }
                    }),
            ])
            ->headerActions([
                ManualCheckInAction::handsOn(),
            ])
            ->recordActions([
                self::checkInAction(),
                self::checkOutAction(),
            ]);
    }

    /**
     * The seminar registration owns participant identity; the hands-on row only
     * carries it for standalone registrations.
     *
     * @see .ai/rules/seminar-registrations-hands-on-registrations.md
     */
    private static function participantName(HandsOnRegistration $record): string
    {
        return $record->seminarRegistration?->name_license
            ?? $record->name_license
            ?? 'N/A';
    }

    private static function checkInAction(): Action
    {
        return Action::make('checkIn')
            ->label(__('filament.attendance.check_in'))
            ->icon('heroicon-o-arrow-left-on-rectangle')
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading(__('filament.attendance.check_in'))
            ->modalDescription(fn (HandsOnRegistration $record): string => __('filament.attendance.check_in_confirm', [
                'name' => self::participantName($record),
            ]))
            ->modalSubmitActionLabel(__('filament.attendance.check_in'))
            // Check-in is its own permission, not part of `update attendances`.
            ->visible(fn (HandsOnRegistration $record): bool => $record->attendance === null
                && (auth()->user()?->can('checkIn', Attendance::class) ?? false))
            ->action(function (HandsOnRegistration $record): void {
                $staff = auth()->user();

                if (! $staff) {
                    return;
                }

                app(AttendanceCheckInService::class)->checkInHandsOn($record, $staff);

                Notification::make()->success()->title(__('filament.attendance.check_in_success', [
                    'name' => self::participantName($record),
                ]))->send();
            });
    }

    private static function checkOutAction(): Action
    {
        return Action::make('checkOut')
            ->label(__('filament.attendance.check_out'))
            ->icon('heroicon-o-arrow-right-on-rectangle')
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading(__('filament.attendance.check_out'))
            ->modalDescription(fn (HandsOnRegistration $record): string => __('filament.attendance.check_out_confirm', [
                'name' => self::participantName($record),
            ]))
            ->modalSubmitActionLabel(__('filament.attendance.check_out'))
            // Check-out is its own permission: the desk may hold it without
            // write access to the attendance records.
            ->visible(fn (HandsOnRegistration $record): bool => $record->attendance?->checked_in_at !== null
                && $record->attendance?->checked_out_at === null
                && (auth()->user()?->can('checkOut', Attendance::class) ?? false))
            ->action(function (HandsOnRegistration $record): void {
                $staff = auth()->user();

                if (! $staff) {
                    return;
                }

                app(AttendanceCheckOutService::class)->checkOutHandsOn($record, $staff);

                Notification::make()->success()->title(__('filament.attendance.checked_out'))->send();
            });
    }
}
