<?php

namespace App\Filament\Resources\Attendances\Widgets;

use App\Models\Attendance;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

/**
 * Per-day check-in / check-out totals for the attendance desk and dashboard.
 *
 * Seminar attendance is keyed on the event day (1-3). Hands-on sessions have no
 * day dimension, so their totals are grouped by the session's event date.
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
        return [
            ...$this->seminarStats(),
            ...$this->handsOnStats(),
        ];
    }

    /**
     * @return array<int, Stat>
     */
    private function seminarStats(): array
    {
        $stats = [];

        foreach (Attendance::SEMINAR_DAYS as $day) {
            $base = Attendance::query()
                ->where('activity_type', 'seminar')
                ->where('day', $day)
                ->whereNull('cancelled_at');

            $checkIns = (clone $base)->whereNotNull('checked_in_at')->count();
            $checkOuts = (clone $base)->whereNotNull('checked_out_at')->count();

            $stats[] = $this->makeStat(
                __('filament.widgets.attendance_daily.day', ['day' => $day]),
                $checkIns,
                $checkOuts,
                __('filament.widgets.attendance_daily.seminar_check_in_out'),
                'info',
            );
        }

        return $stats;
    }

    /**
     * @return array<int, Stat>
     */
    private function handsOnStats(): array
    {
        $rows = Attendance::query()
            ->join('hands_on_registrations as hr', 'hr.id', '=', 'attendances.hands_on_registration_id')
            ->join('hands_ons as ho', 'ho.id', '=', 'hr.hands_on_id')
            ->where('attendances.activity_type', 'hands_on')
            ->whereNull('attendances.cancelled_at')
            ->groupBy('ho.event_date')
            ->orderBy('ho.event_date')
            ->selectRaw('ho.event_date as event_date')
            ->selectRaw('SUM(CASE WHEN attendances.checked_in_at IS NOT NULL THEN 1 ELSE 0 END) as check_ins')
            ->selectRaw('SUM(CASE WHEN attendances.checked_out_at IS NOT NULL THEN 1 ELSE 0 END) as check_outs')
            ->get();

        return $rows
            ->map(fn (object $row): Stat => $this->makeStat(
                Carbon::parse($row->event_date)->format('d M Y'),
                (int) $row->check_ins,
                (int) $row->check_outs,
                __('filament.widgets.attendance_daily.hands_on_check_in_out'),
                'warning',
            ))
            ->all();
    }

    private function makeStat(string $label, int $checkIns, int $checkOuts, string $description, string $color): Stat
    {
        return Stat::make($label, "{$checkIns} / {$checkOuts}")
            ->description($description)
            ->color($color);
    }
}
