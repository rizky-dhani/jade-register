<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\SeminarRegistrations\SeminarRegistrationResource;
use App\Models\SeminarRegistration;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SeminarParticipantCount extends StatsOverviewWidget
{
    protected ?string $heading = 'Seminar Participants';

    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $pending = SeminarRegistration::whereNull('payment_proof_path')->count();
        $needToBeChecked = SeminarRegistration::whereNotNull('payment_proof_path')
            ->where('payment_status', 'pending')
            ->count();
        $verified = SeminarRegistration::where('payment_status', 'verified')->count();
        $total = SeminarRegistration::count();

        return [
            Stat::make('Pending (No Proof)', (string) $pending)
                ->url(SeminarRegistrationResource::getUrl('index', ['tableFilters' => ['has_payment_proof' => ['value' => 0]]])),
            Stat::make('Need to be Checked', (string) $needToBeChecked)
                ->color('warning')
                ->url(SeminarRegistrationResource::getUrl('index', ['tableFilters' => ['has_payment_proof' => ['value' => 1], 'payment_status' => ['value' => 'pending']]])),
            Stat::make('Verified', (string) $verified)
                ->color('success')
                ->url(SeminarRegistrationResource::getUrl('index', ['tableFilters' => ['payment_status' => ['value' => 'verified']]])),
            Stat::make('Total', (string) $total),
        ];
    }
}
