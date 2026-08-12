<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\HandsOnRegistrations\HandsOnRegistrationResource;
use App\Models\HandsOn;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HandsOnRegistrationTotals extends StatsOverviewWidget
{
    protected array|int|null $columns = 5;

    protected static ?int $sort = 1;

    public function getHeading(): string
    {
        return __('filament.widgets.hands_on_participant_count');
    }

    public static function canView(): bool
    {
        return request()->route()?->getName() === 'filament.dashboard.pages.hands-on-registration-report';
    }

    protected function getStats(): array
    {
        $handsOns = HandsOn::withCount([
            'handsOnRegistrations as pending_count' => fn ($q) => $q->where('payment_status', 'pending'),
            'handsOnRegistrations as verified_count' => fn ($q) => $q->where('payment_status', 'verified'),
        ])
            ->orderBy('ho_code')
            ->get();

        if ($handsOns->isEmpty()) {
            return [];
        }

        $stats = [];

        foreach ($handsOns as $handsOn) {
            $total = $handsOn->pending_count + $handsOn->verified_count;
            $filterDate = $handsOn->event_date->format('Y-m-d');
            $pendingLabel = __('filament.widgets.hands_on_participant_count.pending');
            $verifiedLabel = __('filament.widgets.hands_on_participant_count.verified');

            $slots = $handsOn->max_seats !== null
                ? " • Slots {$total}/{$handsOn->max_seats}"
                : '';

            $stats[] = Stat::make($handsOn->ho_code, (string) $total)
                ->description("{$pendingLabel} {$handsOn->pending_count} • {$verifiedLabel} {$handsOn->verified_count}{$slots}")
                ->color(match (true) {
                    $handsOn->pending_count > 0 => 'warning',
                    $handsOn->verified_count > 0 => 'success',
                    default => 'gray',
                })
                ->url(HandsOnRegistrationResource::getUrl('index', [
                    'filters' => [
                        'event_date' => ['value' => $filterDate],
                    ],
                ]));
        }

        return $stats;
    }
}
