<?php

namespace App\Jobs;

use App\Models\DigitalWorkshopRegistration;
use App\Models\DigitalWorkshopRegistrationIntent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class FulfillDigitalWorkshopIntent implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60];

    public function __construct(public DigitalWorkshopRegistrationIntent $intent) {}

    public function handle(): void
    {
        $intent = $this->intent->fresh();

        if (! $intent || ! $intent->isAwaiting() || ! $intent->seminar_registration_id) {
            return;
        }

        // Cheap guard: an earlier dispatch already consumed this intent and left
        // the link behind.
        if ($intent->digital_workshop_registration_id) {
            return;
        }

        $registration = null;

        DB::transaction(function () use ($intent, &$registration) {
            // Re-read under a row lock so two concurrent dispatches cannot both
            // pass the guard above. The lock is on the intent, because the intent
            // is the row being consumed; the registration does not exist yet.
            $locked = DigitalWorkshopRegistrationIntent::whereKey($intent->id)
                ->lockForUpdate()
                ->first();

            if (! $locked || $locked->digital_workshop_registration_id || ! $locked->isAwaiting()) {
                return;
            }

            $workshop = $locked->digitalWorkshop;

            $registration = DigitalWorkshopRegistration::create([
                'digital_workshop_id' => $locked->digital_workshop_id,
                'seminar_registration_id' => $locked->seminar_registration_id,
                'registration_type' => 'bundled',
                'registration_code' => DigitalWorkshopRegistration::generateRegistrationCode(),
                // A cleared bundle price falls back to the standalone price
                // rather than creating a free registration.
                'amount' => $workshop->bundle_price ?? $workshop->price,
                'payment_status' => 'pending',
                'email' => $locked->email,
                'phone' => $locked->phone,
                'name' => $locked->name,
                'name_license' => $locked->name_license,
                'nik' => $locked->nik,
                'pdgi_branch' => $locked->pdgi_branch,
                'kompetensi' => $locked->kompetensi,
                'status' => $locked->status_participant,
                'country_id' => $locked->country_id,
                'language' => $locked->language,
                'payment_method' => $locked->payment_method,
                'payment_proof_path' => $locked->payment_proof_path,
            ]);

            $locked->update([
                'status' => 'fulfilled',
                'digital_workshop_registration_id' => $registration->id,
                'fulfilled_at' => now(),
            ]);
        });

        if (! $registration) {
            return;
        }

        CompleteDigitalWorkshopRegistration::dispatch($registration);
    }
}
