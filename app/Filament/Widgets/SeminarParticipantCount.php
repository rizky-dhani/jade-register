<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\SeminarRegistrations\SeminarRegistrationResource;
use App\Models\SeminarRegistration;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SeminarParticipantCount extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public function getHeading(): string
    {
        return __('filament.widgets.seminar_participant_count');
    }

    public static function canView(): bool
    {
        return ! auth()->user()?->hasRole('Participant');
    }

    protected function getStats(): array
    {
        $pending = SeminarRegistration::whereNull('payment_proof_path')->count();
        $needToBeChecked = SeminarRegistration::whereNotNull('payment_proof_path')
            ->where('payment_status', 'pending')
            ->count();
        $verified = SeminarRegistration::where('payment_status', 'verified')->count();
        $total = SeminarRegistration::count();

        return [
            Stat::make(__('filament.widgets.seminar_participant_count.pending'), (string) $pending)
                ->description(__('filament.widgets.seminar_participant_count.no_payment_proof'))
                ->url(SeminarRegistrationResource::getUrl('index', ['filters' => ['has_payment_proof' => ['value' => 0]]])),
            Stat::make(__('filament.widgets.seminar_participant_count.need_to_be_checked'), (string) $needToBeChecked)
                ->description(__('filament.widgets.seminar_participant_count.awaiting_verification'))
                ->color('warning')
                ->url(SeminarRegistrationResource::getUrl('index', ['filters' => ['has_payment_proof' => ['value' => 1], 'payment_status' => ['value' => 'pending']]])),
            Stat::make(__('filament.widgets.seminar_participant_count.verified'), (string) $verified)
                ->description(__('filament.widgets.seminar_participant_count.payments_verified'))
                ->color('success')
                ->url(SeminarRegistrationResource::getUrl('index', ['filters' => ['payment_status' => ['value' => 'verified']]])),
            Stat::make(__('filament.widgets.seminar_participant_count.total'), (string) $total)
                ->description(__('filament.widgets.seminar_participant_count.all_registrations')),
        ];
    }
}
