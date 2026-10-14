<?php

namespace App\Filament\Widgets;

use App\Models\HandsOn;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HandsOnRegistrationRevenue extends StatsOverviewWidget
{
    protected array|int|null $columns = 4;

    protected static ?int $sort = 4;

    public function getHeading(): string
    {
        return __('filament.widgets.hands_on_registration_revenue');
    }

    public static function canView(): bool
    {
        return request()->route()?->getName() === 'filament.dashboard.pages.hands-on-registration-report';
    }

    protected function getStats(): array
    {
        $handsOns = HandsOn::withCount([
            'handsOnRegistrations as verified_count' => fn ($q) => $q->where('payment_status', 'verified'),
        ])
            ->orderBy('event_date')
            ->get();

        if ($handsOns->isEmpty()) {
            return [];
        }

        $grandTotal = 0;
        $stats = [];

        foreach ($handsOns as $handsOn) {
            $revenue = $handsOn->verified_count * $handsOn->current_price;
            $grandTotal += $revenue;

            $stats[] = Stat::make($handsOn->doctor_name, $this->formatCurrency($revenue, $handsOn->currency))
                ->description(__('filament.widgets.hands_on_registration_revenue.verified_registrations', ['count' => $handsOn->verified_count]))
                ->color($revenue > 0 ? 'success' : 'gray');
        }

        $stats[] = Stat::make(__('filament.widgets.total_revenue'), $this->formatCurrency($grandTotal, $handsOns->first()->currency))
            ->description(__('filament.widgets.hands_on_registration_revenue.all_events'))
            ->descriptionIcon('heroicon-o-banknotes')
            ->color('success');

        return $stats;
    }

    private function formatCurrency(int $amount, string $currency): string
    {
        if ($currency === 'USD') {
            return '$'.number_format($amount, 2);
        }

        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
