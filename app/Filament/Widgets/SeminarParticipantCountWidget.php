<?php

namespace App\Filament\Widgets;

use App\Models\SeminarRegistration;
use Filament\Widgets\Widget;

class SeminarParticipantCountWidget extends Widget
{
    protected static string $view = 'filament.widgets.seminar-participant-count-widget';

    public function getParticipantsData(): array
    {
        $pending = SeminarRegistration::whereNull('payment_proof_path')->count();
        $needToBeChecked = SeminarRegistration::whereNotNull('payment_proof_path')
            ->where('payment_status', 'pending')
            ->count();
        $verified = SeminarRegistration::where('payment_status', 'verified')->count();
        $total = SeminarRegistration::count();

        return [
            'pending' => $pending,
            'needToBeChecked' => $needToBeChecked,
            'verified' => $verified,
            'total' => $total,
        ];
    }
}
