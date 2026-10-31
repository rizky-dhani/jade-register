<?php

namespace App\Services;

use App\Models\LunchRedemption;
use App\Models\SeminarRegistration;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;

class LunchRedemptionService
{
    public function isRedemptionOpen(): bool
    {
        $eventDate = Setting::get('seminar_event_date');

        return $eventDate instanceof Carbon
            && $eventDate->toDateString() === now(config('app.timezone'))->toDateString();
    }

    public function redeem(SeminarRegistration $registration, User $staff): LunchRedemption
    {
        $existing = $registration->lunchRedemption()->first();

        if ($existing) {
            return $existing;
        }

        if (! $registration->isEligibleForLunch()) {
            throw new \DomainException('This registration is not eligible for a lunch coupon.');
        }

        if (! $this->isRedemptionOpen()) {
            throw new \DomainException('Lunch coupon redemption is not open today.');
        }

        try {
            return LunchRedemption::create([
                'seminar_registration_id' => $registration->id,
                'redeemed_at' => now(),
                'redeemed_by' => $staff->id,
                'redeemed_by_name' => $staff->name,
            ]);
        } catch (UniqueConstraintViolationException $exception) {
            $existing = $registration->lunchRedemption()->first();

            if ($existing) {
                return $existing;
            }

            throw $exception;
        }
    }
}
