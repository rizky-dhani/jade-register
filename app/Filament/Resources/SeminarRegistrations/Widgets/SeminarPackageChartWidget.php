<?php

namespace App\Filament\Resources\SeminarRegistrations\Widgets;

use App\Models\Seminar;
use Filament\Widgets\ChartWidget;

class SeminarPackageChartWidget extends ChartWidget
{
    protected ?string $heading = '';

    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return __('seminar.participants_per_package');
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('Super Admin') ?? false;
    }

    protected function getData(): array
    {
        $packages = Seminar::active()
            ->withCount(['seminarRegistrations' => function ($query) {
                $query->whereIn('payment_status', ['pending', 'verified']);
            }])
            ->orderBy('sort_order')
            ->get();

        $labels = [];
        $data = [];

        foreach ($packages as $package) {
            $labels[] = "{$package->name} ({$package->label})";
            $data[] = $package->seminar_registrations_count;
        }

        return [
            'datasets' => [
                [
                    'label' => __('seminar.registered_participants'),
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(245, 158, 11, 0.7)',
                        'rgba(239, 68, 68, 0.7)',
                        'rgba(139, 92, 246, 0.7)',
                        'rgba(236, 72, 153, 0.7)',
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)',
                        'rgb(236, 72, 153)',
                    ],
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
                    'display' => false,
                ],
            ],
        ];
    }
}
