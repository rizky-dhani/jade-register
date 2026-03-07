<?php

namespace App\Filament\Widgets;

use App\Models\SeminarRegistration;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SeminarParticipantCount extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $pending = SeminarRegistration::whereNull('payment_proof_path')->count();
        $needToBeChecked = SeminarRegistration::whereNotNull('payment_proof_path')
            ->where('payment_status', 'pending')
            ->count();
        $verified = SeminarRegistration::where('payment_status', 'verified')->count();
        $total = SeminarRegistration::count();

        return [
            Stat::make('Pending (No Proof)', (string) $pending),
            Stat::make('Need to be Checked', (string) $needToBeChecked)
                ->color('warning'),
            Stat::make('Verified', (string) $verified)
                ->color('success'),
            Stat::make('Total', (string) $total),
        ];
    }
}
