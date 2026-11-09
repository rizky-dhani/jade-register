<?php

namespace App\Filament\Resources\Attendances\Tables;

use App\Models\Attendance;
use App\Models\SeminarRegistration;
use App\Services\AttendanceCheckInService;
use App\Services\AttendanceCheckOutService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * The seminar desk roster: every verified registration gets a row whether or
 * not it has been scanned, with the attendance rows joined in when they exist.
 *
 * The seminar runs for three days, so check-in is per day — each day has its own
 * attendance row and its own check-in column.
 */
class SeminarAttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_license')
                    ->label(__('filament.attendance.participant_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('registration_code')
                    ->label(__('filament.attendance.registration_code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('seminarPackage.name')
                    ->label(__('filament.attendance.seminar_package'))
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                ...self::dayColumns(),
                IconColumn::make('is_checked_out')
                    ->label(__('filament.attendance.checked_out'))
                    ->state(fn (SeminarRegistration $record): bool => $record->seminarAttendances
                        ->contains(fn (Attendance $attendance): bool => $attendance->checked_out_at !== null))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name_license')
            // The record is a registration, not an attendance, so Filament's
            // default record link would point the attendance edit page at a
            // seminar registration id.
            ->recordUrl(null)
            ->filters([
                SelectFilter::make('day')
                    ->label(__('filament.attendance.day'))
                    ->options(self::dayOptions())
                    ->query(function ($query, array $data): void {
                        if (blank($data['value'])) {
                            return;
                        }

                        $day = (int) $data['value'];

                        // Only participants with a row for that day have been
                        // through the desk that day.
                        $query->whereHas('seminarAttendances', fn ($attendance) => $attendance->where('day', $day));
                    }),
                SelectFilter::make('is_checked_in')
                    ->label(__('filament.attendance.check_in_status'))
                    ->options([
                        'checked_in' => __('filament.attendance.checked_in'),
                        'not_checked_in' => __('filament.attendance.not_checked_in'),
                    ])
                    ->query(function ($query, array $data): void {
                        if ($data['value'] === 'checked_in') {
                            $query->whereHas('seminarAttendances', fn ($attendance) => $attendance->whereNotNull('checked_in_at'));
                        } elseif ($data['value'] === 'not_checked_in') {
                            $query->whereDoesntHave('seminarAttendances', fn ($attendance) => $attendance->whereNotNull('checked_in_at'));
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
                            $query->whereHas('seminarAttendances', fn ($attendance) => $attendance->whereNotNull('checked_out_at'));
                        } elseif ($data['value'] === 'not_checked_out') {
                            $query->whereDoesntHave('seminarAttendances', fn ($attendance) => $attendance->whereNotNull('checked_out_at'));
                        }
                    }),
            ])
            ->headerActions([
                ManualCheckInAction::seminar(),
            ])
            ->recordActions([
                ...self::dayCheckInActions(),
                self::checkOutAction(),
            ]);
    }

    /**
     * One icon column per event day, read from the day's attendance row.
     *
     * @return array<int, IconColumn|TextColumn>
     */
    private static function dayColumns(): array
    {
        $columns = [];

        foreach (Attendance::SEMINAR_DAYS as $day) {
            $columns[] = IconColumn::make("day_{$day}_checked_in")
                ->label(__('filament.attendance.day_check_in', ['day' => $day]))
                ->state(fn (SeminarRegistration $record): bool => $record->seminarAttendances
                    ->contains(fn (Attendance $attendance): bool => $attendance->day === $day && $attendance->checked_in_at !== null))
                ->boolean();
            $columns[] = TextColumn::make("day_{$day}_checked_in_at")
                ->label(__('filament.attendance.day_check_in_time', ['day' => $day]))
                ->state(fn (SeminarRegistration $record): ?string => $record->seminarAttendances
                    ->firstWhere('day', $day)?->checked_in_at?->format('d M Y H:i'))
                ->placeholder('-')
                ->toggleable(isToggledHiddenByDefault: true);
        }

        return $columns;
    }

    /**
     * @return array<int, Action>
     */
    private static function dayCheckInActions(): array
    {
        return array_map(
            fn (int $day): Action => Action::make("checkInDay{$day}")
                ->label(__('filament.attendance.day_check_in', ['day' => $day]))
                ->icon('heroicon-o-arrow-left-on-rectangle')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading(__('filament.attendance.day_check_in', ['day' => $day]))
                ->modalDescription(fn (SeminarRegistration $record): string => __('filament.attendance.check_in_confirm', [
                    'name' => $record->name_license,
                ]))
                ->modalSubmitActionLabel(__('filament.attendance.check_in'))
                // Same desk-role gate as ManualCheckInAction: the Admin role is
                // never granted `update attendances`.
                ->visible(fn (SeminarRegistration $record): bool => $record->seminarAttendances->firstWhere('day', $day) === null
                    && (auth()->user()?->hasRole(['Admin', 'Super Admin']) ?? false))
                ->action(function (SeminarRegistration $record) use ($day): void {
                    $staff = auth()->user();

                    if (! $staff) {
                        return;
                    }

                    app(AttendanceCheckInService::class)->checkInSeminar($record, $staff, $day);

                    Notification::make()->success()->title(__('filament.attendance.check_in_success', [
                        'name' => $record->name_license,
                    ]))->send();
                }),
            Attendance::SEMINAR_DAYS,
        );
    }

    /**
     * @return array<int, string>
     */
    private static function dayOptions(): array
    {
        $options = [];

        foreach (Attendance::SEMINAR_DAYS as $day) {
            $options[(string) $day] = __('filament.attendance.day', ['day' => $day]);
        }

        return $options;
    }

    private static function checkOutAction(): Action
    {
        return Action::make('checkOut')
            ->label(__('filament.attendance.check_out'))
            ->icon('heroicon-o-arrow-right-on-rectangle')
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading(__('filament.attendance.check_out'))
            ->modalDescription(fn (SeminarRegistration $record): string => __('filament.attendance.check_out_confirm', [
                'name' => $record->name_license,
            ]))
            ->modalSubmitActionLabel(__('filament.attendance.check_out'))
            // Gated on the desk role, not on `update attendances`: the Admin
            // role is never granted that permission, and the same people check
            // participants out on the verify screen (AttendanceVerify::canCheckIn).
            ->visible(fn (SeminarRegistration $record): bool => $record->seminarAttendances
                ->contains(fn (Attendance $attendance): bool => $attendance->checked_in_at !== null && $attendance->checked_out_at === null)
                && (auth()->user()?->hasRole(['Admin', 'Super Admin']) ?? false))
            ->action(function (SeminarRegistration $record): void {
                $staff = auth()->user();

                if (! $staff) {
                    return;
                }

                app(AttendanceCheckOutService::class)->checkOutSeminar($record, $staff);

                Notification::make()->success()->title(__('filament.attendance.checked_out'))->send();
            });
    }
}
