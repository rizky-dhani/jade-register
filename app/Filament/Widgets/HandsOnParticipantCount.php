<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\HandsOnRegistrations\HandsOnRegistrationResource;
use App\Models\HandsOn;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HandsOnParticipantCount extends StatsOverviewWidget
{
    protected array|int|null $columns = 4;

    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return __('filament.widgets.hands_on_participant_count');
    }

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

        $totalPending = $handsOns->flatten()->sum('pending_count');
        $totalVerified = $handsOns->flatten()->sum('verified_count');
        $stats = [];

        foreach ($handsOns as $group) {
            $handsOn = $group->first();
            $dateLabel = $handsOn->event_date->format('d M Y');
            $filterDate = $handsOn->event_date->format('Y-m-d');
            $pendingTotal = $group->sum('pending_count');

            $stats[] = Stat::make($dateLabel, (string) $pendingTotal)
                ->description(__('filament.widgets.hands_on_participant_count.pending'))
                ->color('warning')
                ->url(HandsOnRegistrationResource::getUrl('index', [
                    'filters' => [
                        'event_date' => ['value' => $filterDate],
                        'payment_status' => ['value' => 'pending'],
                    ],
                ]));
        }

        $stats[] = Stat::make(__('filament.widgets.total_pending'), (string) $totalPending)
            ->description(__('filament.widgets.hands_on_participant_count.pending'))
            ->descriptionIcon('heroicon-o-clock')
            ->color('warning');

        foreach ($handsOns as $group) {
            $handsOn = $group->first();
            $dateLabel = $handsOn->event_date->format('d M Y');
            $filterDate = $handsOn->event_date->format('Y-m-d');
            $verifiedTotal = $group->sum('verified_count');

            $stats[] = Stat::make($dateLabel, (string) $verifiedTotal)
                ->description(__('filament.widgets.hands_on_participant_count.verified'))
                ->color('success')
                ->url(HandsOnRegistrationResource::getUrl('index', [
                    'filters' => [
                        'event_date' => ['value' => $filterDate],
                        'payment_status' => ['value' => 'verified'],
                    ],
                ]));
        }

        $stats[] = Stat::make(__('filament.widgets.total_verified'), (string) $totalVerified)
            ->description(__('filament.widgets.hands_on_participant_count.verified'))
            ->descriptionIcon('heroicon-o-check-circle')
            ->color('success');

        return $stats;
    }
}
