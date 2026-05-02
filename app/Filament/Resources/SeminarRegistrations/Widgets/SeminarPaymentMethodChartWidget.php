<?php

namespace App\Filament\Resources\SeminarRegistrations\Widgets;

use App\Models\SeminarRegistration;
use Filament\Widgets\ChartWidget;

class SeminarPaymentMethodChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected function getHeading(): string
    {
        return __('seminar.payment_method');
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('Super Admin') ?? false;
    }

    protected function getData(): array
    {
        $registrations = SeminarRegistration::selectRaw('payment_method, COUNT(*) as count')
            ->whereIn('payment_status', ['pending', 'verified'])
            ->groupBy('payment_method')
            ->get();

        $labels = [];
        $data = [];

        foreach ($registrations as $reg) {
            $labels[] = match ($reg->payment_method) {
                'bank_transfer' => 'Transfer Bank',
                'qris' => 'QRIS',
                default => $reg->payment_method ?? '-',
            };
            $data[] = (int) $reg->count;
        }

        return [
            'datasets' => [
                [
                    'label' => __('seminar.registered_participants'),
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(16, 185, 129, 0.7)',
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
