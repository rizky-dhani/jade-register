<?php

namespace App\Services;

use App\Models\SeminarRegistration;
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

    public function validate(string $token): ?SeminarRegistration
    {
        $registration = SeminarRegistration::where('qr_token', $token)->first();

        if (! $registration) {
            return null;
        }

        return $registration;
    }

    public function isExpired(SeminarRegistration $registration): bool
    {
        return $registration->qr_expires_at && $registration->qr_expires_at->isPast();
    }

    public function calculateExpiration(SeminarRegistration $registration): \Carbon\Carbon
    {
        $lastEventDate = $this->getLastEventDate($registration);

        return $lastEventDate->addDay()->endOfDay();
    }

    private function getLastEventDate(SeminarRegistration $registration): \Carbon\Carbon
    {
        $eventEndDate = now()->set(2026, 11, 15);

        $handsOnDates = $registration->handsOnRegistrations()
            ->where('payment_status', 'verified')
            ->with('handsOn')
            ->get()
            ->pluck('handsOn.event_date')
            ->filter()
            ->map(fn ($date) => \Carbon\Carbon::parse($date));

        if ($handsOnDates->isNotEmpty()) {
            return $handsOnDates->max();
        }

        return $eventEndDate;
    }

    public function getQrUrl(SeminarRegistration $registration): ?string
    {
        if (! $registration->qr_token) {
            return null;
        }

        return url('/attendance/qr-code/'.$registration->qr_token);
    }

    public function getVerifyUrl(SeminarRegistration $registration): ?string
    {
        if (! $registration->qr_token) {
            return null;
        }

        return url('/attendance/verify/'.$registration->qr_token);
    }
}
