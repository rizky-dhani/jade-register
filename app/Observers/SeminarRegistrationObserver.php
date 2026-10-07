<?php

namespace App\Observers;

use App\Jobs\FulfillDigitalWorkshopIntent;
use App\Models\DigitalWorkshopRegistrationIntent;
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

            // Digital Workshop bundle intents waiting on this email are now
            // claimable. Dispatch rather than fulfil inline: the observer runs
            // inside the admin's verification request, and creating a
            // registration plus queueing email there would make a mail failure
            // look like a payment-verification failure.
            DigitalWorkshopRegistrationIntent::awaitingForEmail($registration->email)
                ->get()
                ->each(function (DigitalWorkshopRegistrationIntent $intent) use ($registration): void {
                    // Link before dispatch so the job reads a committed value.
                    $intent->update(['seminar_registration_id' => $registration->id]);

                    FulfillDigitalWorkshopIntent::dispatch($intent);
                });
        }
    }
}
