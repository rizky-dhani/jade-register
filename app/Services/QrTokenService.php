<?php

namespace App\Services;

use App\Models\DigitalWorkshopRegistration;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use Carbon\Carbon;
use Illuminate\Support\Str;

class QrTokenService
{
    public function generate(SeminarRegistration $registration): void
    {
        do {
            $token = Str::random(64);
        } while (SeminarRegistration::where('qr_token', $token)->exists());

        $registration->update([
            'qr_token' => $token,
            'qr_expires_at' => $this->calculateExpiration($registration),
        ]);
    }

    public function generateForHandsOn(HandsOnRegistration $registration): void
    {
        do {
            $token = Str::random(64);
        } while (
            SeminarRegistration::where('qr_token', $token)->exists() ||
            HandsOnRegistration::where('qr_token', $token)->exists()
        );

        $registration->update([
            'qr_token' => $token,
            'qr_expires_at' => $this->calculateExpirationForHandsOn($registration),
        ]);
    }

    public function generateForDigitalWorkshop(DigitalWorkshopRegistration $registration): void
    {
        // All three tables: validate() scans them in order, so a token that also
        // existed elsewhere would resolve to the wrong record.
        do {
            $token = Str::random(64);
        } while (
            SeminarRegistration::where('qr_token', $token)->exists() ||
            HandsOnRegistration::where('qr_token', $token)->exists() ||
            DigitalWorkshopRegistration::where('qr_token', $token)->exists()
        );

        $registration->update([
            'qr_token' => $token,
            'qr_expires_at' => $this->calculateExpirationForDigitalWorkshop($registration),
        ]);
    }

    public function validate(string $token): SeminarRegistration|HandsOnRegistration|DigitalWorkshopRegistration|null
    {
        $registration = SeminarRegistration::where('qr_token', $token)->first();

        if ($registration) {
            return $registration;
        }

        $handsOn = HandsOnRegistration::where('qr_token', $token)->first();

        if ($handsOn) {
            return $handsOn;
        }

        return DigitalWorkshopRegistration::where('qr_token', $token)->first();
    }

    public function isExpired(SeminarRegistration|HandsOnRegistration|DigitalWorkshopRegistration $registration): bool
    {
        return $registration->qr_expires_at && $registration->qr_expires_at->isPast();
    }

    public function calculateExpiration(SeminarRegistration $registration): Carbon
    {
        $lastEventDate = $this->getLastEventDate($registration);

        return $lastEventDate->addDay()->endOfDay();
    }

    public function calculateExpirationForHandsOn(HandsOnRegistration $registration): Carbon
    {
        $eventDate = $registration->handsOn?->event_date
            ? Carbon::parse($registration->handsOn->event_date)
            : Carbon::create(2026, 11, 15);

        return $eventDate->addDay()->endOfDay();
    }

    public function calculateExpirationForDigitalWorkshop(DigitalWorkshopRegistration $registration): Carbon
    {
        $eventDate = $registration->digitalWorkshop?->event_date
            ? Carbon::parse($registration->digitalWorkshop->event_date)
            : Carbon::create(2026, 11, 21);

        return $eventDate->addDay()->endOfDay();
    }

    private function getLastEventDate(SeminarRegistration $registration): Carbon
    {
        $eventEndDate = Carbon::create(2026, 11, 15);

        $handsOnDates = $registration->handsOnRegistrations()
            ->where('payment_status', 'verified')
            ->with('handsOn')
            ->get()
            ->pluck('handsOn.event_date')
            ->filter()
            ->map(fn ($date) => Carbon::parse($date));

        if ($handsOnDates->isNotEmpty()) {
            return $handsOnDates->max();
        }

        return $eventEndDate;
    }

    public function getQrUrl(SeminarRegistration|HandsOnRegistration|DigitalWorkshopRegistration $registration): ?string
    {
        if (! $registration->qr_token) {
            return null;
        }

        return url('/attendance/qr-code/'.$registration->qr_token);
    }

    public function getVerifyUrl(SeminarRegistration|HandsOnRegistration|DigitalWorkshopRegistration $registration): ?string
    {
        if (! $registration->qr_token) {
            return null;
        }

        return url('/attendance/verify/'.$registration->qr_token);
    }
}
