<?php

namespace App\Filament\Widgets;

use App\Models\SeminarRegistration;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SeminarPackageStatsWidget extends StatsOverviewWidget
{
    protected ?string $heading = '';

    protected static ?int $sort = 1;

    public function getHeading(): string
    {
        return __('seminar.seminar_package_statistics');
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['Super Admin', 'Admin']) ?? false;
    }

    protected function getStats(): array
    {
        $registrations = SeminarRegistration::with('seminarPackage')->get();
        $totalRevenue = 0;
        $totalParticipants = 0;

        foreach ($registrations as $registration) {
            $seminar = $registration->seminarPackage;
            if ($seminar) {
                $totalParticipants++;
                // Check if early bird was active at registration time
                $wasEarlyBird = $seminar->early_bird_deadline !== null
                    && $registration->created_at < $seminar->early_bird_deadline;

                if ($wasEarlyBird && $seminar->discounted_price) {
                    $totalRevenue += $seminar->discounted_price;
                } else {
                    $totalRevenue += $seminar->original_price ?? 0;
                }
            }
        }

        $totalRegistrations = SeminarRegistration::count();
        $verifiedRegistrations = SeminarRegistration::where('payment_status', 'verified')->count();
        $pendingRegistrations = SeminarRegistration::where('payment_status', 'pending')->count();

        return [
            Stat::make('Total Registrations', number_format($totalRegistrations))
                ->description(__('seminar.total_participants_description'))
                ->color('primary'),
            Stat::make('Verified Payments', number_format($verifiedRegistrations))
                ->description(__('seminar.verified_payments_description'))
                ->color('success'),
            Stat::make('Pending Payments', number_format($pendingRegistrations))
                ->description(__('seminar.pending_payments_description'))
                ->color('warning'),
            Stat::make('Total Revenue', $this->formatCurrency($totalRevenue))
                ->description(__('seminar.total_revenue_description'))
                ->color('success'),
        ];
    }

    private function formatCurrency(int $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
