<?php

namespace App\Observers;

use App\Models\SeminarRegistration;
use Illuminate\Support\Facades\Log;

class SeminarRegistrationObserver
{
    public function updated(SeminarRegistration $registration): void
    {
        if (
            $registration->wasChanged('payment_status')
            && $registration->payment_status === 'verified'
            && $registration->getOriginal('payment_status') !== 'verified'
        ) {
            $registration->handsOnRegistrations()
                ->where('payment_status', 'pending')
                ->update([
                    'payment_status' => 'verified',
                    'verified_at' => now(),
                ]);

            Log::info('Auto-verified HandsOnRegistrations for seminar registration', [
                'seminar_registration_code' => $registration->registration_code,
            ]);
        }
    }
}
