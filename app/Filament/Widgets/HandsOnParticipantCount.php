<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\HandsOnRegistrations\HandsOnRegistrationResource;
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

        foreach ($handsOns as $group) {
            $handsOn = $group->first();
            $dateLabel = $handsOn->event_date->format('M j, Y');
            $filterDate = $handsOn->event_date->format('Y-m-d');
            $pendingTotal = $group->sum('pending_count');

            $stats[] = Stat::make("Pending — {$dateLabel}", (string) $pendingTotal)
                ->color('warning')
                ->url(HandsOnRegistrationResource::getUrl('index', [
                    'filters' => [
                        'event_date' => ['value' => $filterDate],
                        'payment_status' => ['value' => 'pending'],
                    ],
                ]));
        }

        foreach ($handsOns as $group) {
            $handsOn = $group->first();
            $dateLabel = $handsOn->event_date->format('M j, Y');
            $filterDate = $handsOn->event_date->format('Y-m-d');
            $verifiedTotal = $group->sum('verified_count');

            $stats[] = Stat::make("Verified — {$dateLabel}", (string) $verifiedTotal)
                ->color('success')
                ->url(HandsOnRegistrationResource::getUrl('index', [
                    'filters' => [
                        'event_date' => ['value' => $filterDate],
                        'payment_status' => ['value' => 'verified'],
                    ],
                ]));
        }

        return $stats;
    }
}
