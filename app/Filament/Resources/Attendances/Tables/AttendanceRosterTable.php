<?php

namespace App\Filament\Resources\Attendances\Tables;

use App\Models\Attendance;
use App\Models\AttendanceRoster;
use App\Models\HandsOnRegistration;
use App\Services\AttendanceCancelService;
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
                TextColumn::make('seminar_package')
                    ->label(__('filament.attendance.seminar_package'))
                    ->state(fn (AttendanceRoster $record): string => self::seminarPackageLabel($record))
                    ->wrap()
                    ->placeholder('-'),
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
                ...self::seminarDayCancelActions(),
                self::handsOnCheckInAction(),
                self::handsOnCancelAction(),
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
     * The seminar package the participant registered for. A standalone hands-on
     * registration has no seminar registration, so it has no package.
     */
    private static function seminarPackageLabel(AttendanceRoster $record): string
    {
        $package = $record->seminarRegistration?->seminarPackage;

        if (! $package) {
            return '-';
        }

        return $package->name.' ('.$package->label.')';
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
                // The desk reads sessions by their short code (HO-03), not the
                // full course title, which is too long for a table cell.
                $label = $registration->handsOn?->ho_code
                    ?? $registration->handsOn?->name
                    ?? $registration->registration_code;

                return $registration->activeAttendance?->checked_in_at !== null
                    ? $label.' ('.__('filament.attendance.checked_in_short').')'
                    : $label;
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
            ->map(fn (HandsOnRegistration $registration) => $registration->activeAttendance?->checked_in_at);

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
                ->contains(fn (HandsOnRegistration $registration): bool => $registration->activeAttendance === null)
                && (auth()->user()?->can('checkIn', Attendance::class) ?? false))
            ->action(function (AttendanceRoster $record): void {
                $staff = auth()->user();

                if (! $staff) {
                    return;
                }

                $service = app(AttendanceCheckInService::class);

                foreach (self::handsOnSessions($record) as $registration) {
                    if ($registration->activeAttendance === null) {
                        $service->checkInHandsOn($registration, $staff);
                    }
                }

                Notification::make()->success()->title(__('filament.attendance.check_in_success', [
                    'name' => $record->participant_name,
                ]))->send();
            });
    }

    /**
     * Cancels a day that was checked in by mistake, leaving that day open again.
     * One action per day, so correcting a single wrong day does not disturb the
     * others.
     *
     * @return array<int, Action>
     */
    private static function seminarDayCancelActions(): array
    {
        return array_map(
            fn (int $day): Action => Action::make("cancelCheckInDay{$day}")
                ->label(__('filament.attendance.cancel_check_in_day', ['day' => $day]))
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading(__('filament.attendance.cancel_check_in_day', ['day' => $day]))
                ->modalDescription(fn (AttendanceRoster $record): string => __('filament.attendance.cancel_check_in_confirm', [
                    'name' => $record->participant_name,
                    'day' => $day,
                ]))
                ->modalSubmitActionLabel(__('filament.attendance.cancel_check_in'))
                // Only a day that is actually checked in can be cancelled.
                ->visible(fn (AttendanceRoster $record): bool => $record->isSeminar()
                    && $record->seminarAttendanceForDay($day) !== null
                    && (auth()->user()?->can('checkIn', Attendance::class) ?? false))
                ->action(function (AttendanceRoster $record) use ($day): void {
                    $staff = auth()->user();
                    $registration = $record->seminarRegistration;

                    if (! $staff || ! $registration) {
                        return;
                    }

                    $cancelled = app(AttendanceCancelService::class)
                        ->cancelSeminarDay($registration, $staff, $day);

                    $notification = Notification::make();

                    $cancelled
                        ? $notification->success()->title(__('filament.attendance.cancel_check_in_success', [
                            'name' => $record->participant_name,
                        ]))
                        : $notification->warning()->title(__('filament.attendance.nothing_to_cancel'));

                    $notification->send();
                }),
            Attendance::SEMINAR_DAYS,
        );
    }

    /**
     * Cancels the check-in for the row's hands-on session(s).
     */
    private static function handsOnCancelAction(): Action
    {
        return Action::make('cancelCheckInHandsOn')
            ->label(__('filament.attendance.cancel_check_in_hands_on'))
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(__('filament.attendance.cancel_check_in_hands_on'))
            ->modalDescription(fn (AttendanceRoster $record): string => __('filament.attendance.cancel_check_in_confirm_hands_on', [
                'name' => $record->participant_name,
            ]))
            ->modalSubmitActionLabel(__('filament.attendance.cancel_check_in'))
            ->visible(fn (AttendanceRoster $record): bool => self::handsOnSessions($record)
                ->contains(fn (HandsOnRegistration $registration): bool => $registration->activeAttendance !== null)
                && (auth()->user()?->can('checkIn', Attendance::class) ?? false))
            ->action(function (AttendanceRoster $record): void {
                $staff = auth()->user();

                if (! $staff) {
                    return;
                }

                $service = app(AttendanceCancelService::class);
                $cancelled = false;

                foreach (self::handsOnSessions($record) as $registration) {
                    if ($service->cancelHandsOn($registration, $staff)) {
                        $cancelled = true;
                    }
                }

                $notification = Notification::make();

                $cancelled
                    ? $notification->success()->title(__('filament.attendance.cancel_check_in_success', [
                        'name' => $record->participant_name,
                    ]))
                    : $notification->warning()->title(__('filament.attendance.nothing_to_cancel'));

                $notification->send();
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
                    $attendance = $registration->activeAttendance;

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
            ->contains(fn (HandsOnRegistration $registration): bool => $registration->activeAttendance?->checked_in_at !== null
                && $registration->activeAttendance?->checked_out_at === null);
    }
}
