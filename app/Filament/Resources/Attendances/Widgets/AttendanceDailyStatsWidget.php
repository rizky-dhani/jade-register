<?php

namespace App\Filament\Resources\Attendances\Widgets;

use App\Models\Attendance;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Per-day check-in / check-out totals for the attendance desk.
 *
 * Only seminar attendances carry a real event day; hands-on rows default to
 * day 1 and would inflate that day, so they are excluded here.
 */
class AttendanceDailyStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    public function getHeading(): string
    {
        return __('filament.widgets.attendance_daily.heading');
    }

    public static function canView(): bool
    {
        return ! auth()->user()?->hasRole('Participant');
    }

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $stats = [];

        foreach (Attendance::SEMINAR_DAYS as $day) {
            $base = Attendance::query()
                ->where('activity_type', 'seminar')
                ->where('day', $day)
                ->whereNull('cancelled_at');

            $checkIns = (clone $base)->whereNotNull('checked_in_at')->count();
            $checkOuts = (clone $base)->whereNotNull('checked_out_at')->count();

            $stats[] = Stat::make(
                __('filament.widgets.attendance_daily.day', ['day' => $day]),
                "{$checkIns} / {$checkOuts}"
            )
                ->description(__('filament.widgets.attendance_daily.check_in_out'))
                ->color('success');
        }

        return $stats;
    }
}
