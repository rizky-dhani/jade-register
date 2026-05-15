<?php

namespace App\Filament\Widgets;

use App\Models\HandsOn;
use Filament\Widgets\ChartWidget;

class HandsOnRegistrationStatusChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        return __('filament.widgets.hands_on_participant_count');
    }

    public static function canView(): bool
    {
        return ! auth()->user()?->hasRole('Participant');
    }

    protected function getData(): array
    {
        $handsOns = HandsOn::withCount([
            'handsOnRegistrations as pending_count' => fn ($q) => $q->where('payment_status', 'pending'),
            'handsOnRegistrations as verified_count' => fn ($q) => $q->where('payment_status', 'verified'),
        ])
            ->orderBy('event_date')
            ->get();

        $labels = [];
        $pendingData = [];
        $verifiedData = [];

        foreach ($handsOns as $handsOn) {
            $labels[] = $handsOn->name;
            $pendingData[] = (int) $handsOn->pending_count;
            $verifiedData[] = (int) $handsOn->verified_count;
        }

        return [
            'datasets' => [
                [
                    'label' => __('filament.widgets.hands_on_participant_count.pending'),
                    'data' => $pendingData,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.7)',
                    'borderColor' => 'rgb(245, 158, 11)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => __('filament.widgets.hands_on_participant_count.verified'),
                    'data' => $verifiedData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.7)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
