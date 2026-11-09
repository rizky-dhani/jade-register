<?php

namespace App\Filament\Resources\Attendances\Tables;

use App\Models\Attendance;
use App\Models\AttendanceRoster;
use App\Models\HandsOnRegistration;
use App\Services\AttendanceCheckInService;
use App\Services\AttendanceCheckOutService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The combined attendance desk: every verified participant — seminar and
 * hands-on — in one table, one row each.
 *
 * Rows come from the `attendance_roster` view, so a row is a participant rather
 * than an attendance record. The page eager loads the underlying registrations;
 * nothing here queries per cell.
 */
class AttendanceRosterTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('participant_name')
                    ->label(__('filament.attendance.participant_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('registration_code')
                    ->label(__('filament.attendance.registration_code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('activity_type')
                    ->label(__('filament.attendance.activity_type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __("filament.attendance.activity_{$state}"))
                    ->color(fn (string $state): string => $state === 'seminar' ? 'info' : 'warning')
                    ->sortable(),
                ...self::seminarDayColumns(),
                TextColumn::make('hands_on_sessions')
                    ->label(__('filament.attendance.hands_on_sessions'))
                    ->state(fn (AttendanceRoster $record): string => self::handsOnSummary($record))
                    ->wrap()
                    ->placeholder('-'),
                TextColumn::make('last_check_in')
                    ->label(__('filament.attendance.last_check_in'))
                    ->state(fn (AttendanceRoster $record): ?string => self::lastCheckIn($record))
                    ->placeholder('-'),
            ])
            ->defaultSort('participant_name')
            // A view row has no record page, and Filament's default would point
            // the attendance edit URL at a seminar registration id.
            ->recordUrl(null)
            ->filters([
                SelectFilter::make('activity_type')
                    ->label(__('filament.attendance.activity_type'))
                    ->options([
                        'seminar' => __('filament.attendance.activity_seminar'),
                        'hands_on' => __('filament.attendance.activity_hands_on'),
                    ]),
            ])
            ->headerActions([
                ManualCheckInAction::seminar(),
                ManualCheckInAction::handsOn(),
            ])
            // recordActions() does not flatten nested arrays, so the day
            // actions are spread rather than nested.
            ->recordActions([
                ...self::seminarDayCheckInActions(),
                self::handsOnCheckInAction(),
                self::checkOutAction(),
            ]);
    }

    /**
     * One day column per event day. Seminar rows show checked-in or "-";
     * hands-on rows have no day dimension and stay "-".
     *
     * @return array<int, TextColumn>
     */
    private static function seminarDayColumns(): array
    {
        $columns = [];

        foreach (Attendance::SEMINAR_DAYS as $day) {
            $columns[] = TextColumn::make("day_{$day}")
                ->label(__('filament.attendance.day_number', ['day' => $day]))
                ->badge()
                ->state(fn (AttendanceRoster $record): string => self::seminarDayState($record, $day) ?? '-')
                ->color(fn (AttendanceRoster $record): string => self::seminarDayState($record, $day) === null ? 'gray' : 'success');
        }

        return $columns;
    }

    private static function seminarDayState(AttendanceRoster $record, int $day): ?string
    {
        $attendance = $record->seminarAttendanceForDay($day);

        return $attendance?->checked_in_at !== null
            ? $attendance->checked_in_at->format('d M H:i')
            : null;
    }

    /**
     * @return Collection<int, HandsOnRegistration>
     */
    private static function handsOnSessions(AttendanceRoster $record): Collection
    {
        return $record->resolvedHandsOnRegistrations();
    }

    /**
     * Hands-on sessions with a check-in marker, e.g. "Implant (in), Endo".
     */
    private static function handsOnSummary(AttendanceRoster $record): string
    {
        $sessions = self::handsOnSessions($record);

        if ($sessions->isEmpty()) {
            return '-';
        }

        return $sessions
            ->map(function (HandsOnRegistration $registration): string {
                $name = $registration->handsOn?->name ?? $registration->registration_code;

                return $registration->attendance?->checked_in_at !== null
                    ? $name.' ('.__('filament.attendance.checked_in_short').')'
                    : $name;
            })
            ->join(', ');
    }

    private static function lastCheckIn(AttendanceRoster $record): ?string
    {
        $stamps = self::allCheckIns($record);

        $latest = $stamps->filter()->max();

        return $latest?->format('d M Y H:i');
    }

    /**
     * Every check-in stamp the participant holds, seminar days and hands-on
     * sessions alike.
     *
     * @return Collection<int, Carbon>
     */
    private static function allCheckIns(AttendanceRoster $record): Collection
    {
        $seminar = $record->seminarRegistration?->seminarAttendances
            ?->pluck('checked_in_at') ?? collect();

        $handsOn = self::handsOnSessions($record)
            ->map(fn (HandsOnRegistration $registration) => $registration->attendance?->checked_in_at);

        return $seminar->concat($handsOn)->filter();
    }

    /**
     * @return array<int, Action>
     */
    private static function seminarDayCheckInActions(): array
    {
        return array_map(
            fn (int $day): Action => Action::make("checkInDay{$day}")
                ->label(__('filament.attendance.day_check_in', ['day' => $day]))
                ->icon('heroicon-o-arrow-left-on-rectangle')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading(__('filament.attendance.day_check_in', ['day' => $day]))
                ->modalSubmitActionLabel(__('filament.attendance.check_in'))
                ->visible(fn (AttendanceRoster $record): bool => $record->isSeminar()
                    && $record->seminarAttendanceForDay($day) === null
                    && (auth()->user()?->can('checkIn', Attendance::class) ?? false))
                ->action(function (AttendanceRoster $record) use ($day): void {
                    $staff = auth()->user();
                    $registration = $record->seminarRegistration;

                    if (! $staff || ! $registration) {
                        return;
                    }

                    app(AttendanceCheckInService::class)->checkInSeminar($registration, $staff, $day);

                    Notification::make()->success()->title(__('filament.attendance.check_in_success', [
                        'name' => $record->participant_name,
                    ]))->send();
                }),
            Attendance::SEMINAR_DAYS,
        );
    }

    /**
     * One button for the participant's hands-on sessions: the combined row is a
     * participant, so a per-session button would need a button per session. It
     * checks in every session that is still open and is idempotent per session.
     */
    private static function handsOnCheckInAction(): Action
    {
        return Action::make('checkInHandsOn')
            ->label(__('filament.attendance.check_in_hands_on'))
            ->icon('heroicon-o-wrench-screwdriver')
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading(__('filament.attendance.check_in_hands_on'))
            ->modalSubmitActionLabel(__('filament.attendance.check_in'))
            ->visible(fn (AttendanceRoster $record): bool => self::handsOnSessions($record)
                ->contains(fn (HandsOnRegistration $registration): bool => $registration->attendance === null)
                && (auth()->user()?->can('checkIn', Attendance::class) ?? false))
            ->action(function (AttendanceRoster $record): void {
                $staff = auth()->user();

                if (! $staff) {
                    return;
                }

                $service = app(AttendanceCheckInService::class);

                foreach (self::handsOnSessions($record) as $registration) {
                    if ($registration->attendance === null) {
                        $service->checkInHandsOn($registration, $staff);
                    }
                }

                Notification::make()->success()->title(__('filament.attendance.check_in_success', [
                    'name' => $record->participant_name,
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
            ->modalSubmitActionLabel(__('filament.attendance.check_out'))
            ->visible(fn (AttendanceRoster $record): bool => self::hasOpenCheckOut($record)
                && (auth()->user()?->can('checkOut', Attendance::class) ?? false))
            ->action(function (AttendanceRoster $record): void {
                $staff = auth()->user();

                if (! $staff) {
                    return;
                }

                $service = app(AttendanceCheckOutService::class);

                if ($record->isSeminar() && $record->seminarRegistration) {
                    $service->checkOutSeminar($record->seminarRegistration, $staff);
                }

                foreach (self::handsOnSessions($record) as $registration) {
                    $attendance = $registration->attendance;

                    if ($attendance?->checked_in_at !== null && $attendance->checked_out_at === null) {
                        $service->checkOutHandsOn($registration, $staff);
                    }
                }

                Notification::make()->success()->title(__('filament.attendance.checked_out'))->send();
            });
    }

    private static function hasOpenCheckOut(AttendanceRoster $record): bool
    {
        $seminarOpen = $record->seminarRegistration?->seminarAttendances
            ->contains(fn (Attendance $attendance): bool => $attendance->checked_in_at !== null && $attendance->checked_out_at === null) ?? false;

        return $seminarOpen || self::handsOnSessions($record)
            ->contains(fn (HandsOnRegistration $registration): bool => $registration->attendance?->checked_in_at !== null
                && $registration->attendance?->checked_out_at === null);
    }
}
