<?php

namespace App\Filament\Widgets;

use App\Models\HandsOn;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HandsOnParticipantCount extends StatsOverviewWidget
{
    protected ?string $heading = 'Hands-On Participants';

    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return ! auth()->user()?->hasRole('Participant');
    }

    protected function getStats(): array
    {
        $dates = HandsOn::distinct()
            ->orderBy('event_date')
            ->pluck('event_date')
            ->take(3);

        if ($dates->isEmpty()) {
            return [];
        }

        $handsOns = HandsOn::withCount([
            'handsOnRegistrations as pending_count' => fn ($q) => $q->where('payment_status', 'pending'),
            'handsOnRegistrations as verified_count' => fn ($q) => $q->where('payment_status', 'verified'),
        ])
            ->whereIn('event_date', $dates)
            ->orderBy('event_date')
            ->get()
            ->groupBy('event_date');

        $stats = [];

        foreach ($handsOns as $date => $group) {
            $dateLabel = $date->format('M j, Y');
            $pendingTotal = $group->sum('pending_count');

            $stats[] = Stat::make("Pending — {$dateLabel}", (string) $pendingTotal)
                ->color('warning');
        }

        foreach ($handsOns as $date => $group) {
            $dateLabel = $date->format('M j, Y');
            $verifiedTotal = $group->sum('verified_count');

            $stats[] = Stat::make("Verified — {$dateLabel}", (string) $verifiedTotal)
                ->color('success');
        }

        return $stats;
    }
}
