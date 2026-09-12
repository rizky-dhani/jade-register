<?php

namespace App\Filament\Resources\SeminarRegistrations\Widgets;

use App\Models\SeminarRegistration;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class SeminarCompetencyStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    public function getHeading(): string
    {
        return __('seminar.competency_statistics');
    }

    public static function canView(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user instanceof User && $user->hasRole('Super Admin');
    }

    protected function getStats(): array
    {
        $counts = SeminarRegistration::selectRaw('kompetensi, COUNT(*) as count')
            ->whereNotNull('kompetensi')
            ->where('kompetensi', '!=', '')
            ->groupBy('kompetensi')
            ->orderBy('kompetensi')
            ->pluck('count', 'kompetensi');

        return $counts
            ->map(fn (int $count, string $kompetensi): Stat => Stat::make($kompetensi, number_format($count))
                ->description(__('seminar.registered_participants'))
                ->color('primary'))
            ->values()
            ->all();
    }
}
