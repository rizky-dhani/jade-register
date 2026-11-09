<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use App\Models\User;

class AttendanceCheckInService
{
    /**
     * Record the seminar check-in for one day of the event.
     *
     * The seminar runs for three days and a participant is expected at each, so
     * the row is keyed on (registration, day). Idempotent by lookup:
     * `firstOrCreate` keeps a second scan on the same day — or a manual check-in
     * after a scan — from creating a duplicate row or overwriting the original
     * time and staff member.
     */
    public function checkInSeminar(SeminarRegistration $registration, User $staff, int $day = 1): Attendance
    {
        $this->ensureVerified($registration->payment_status);
        $this->ensureValidDay($day);

        return Attendance::firstOrCreate(
            [
                'seminar_registration_id' => $registration->id,
                'hands_on_registration_id' => null,
                'activity_type' => 'seminar',
                'day' => $day,
            ],
            [
                'checked_in_at' => now(),
                'checked_in_by' => $staff->id,
            ],
        );
    }

    /**
     * Record the hands-on check-in for a single session registration.
     *
     * The unique index cannot guard these rows because
     * `seminar_registration_id` is nullable, so the lookup is what keeps a
     * double scan idempotent. Hands-on sessions have no day dimension.
     */
    public function checkInHandsOn(HandsOnRegistration $registration, User $staff): Attendance
    {
        $this->ensureVerified($registration->payment_status);

        return Attendance::firstOrCreate(
            [
                'hands_on_registration_id' => $registration->id,
                'activity_type' => 'hands_on',
            ],
            [
                'seminar_registration_id' => $registration->seminar_registration_id,
                'day' => 1,
                'checked_in_at' => now(),
                'checked_in_by' => $staff->id,
            ],
        );
    }

    private function ensureVerified(?string $paymentStatus): void
    {
        if ($paymentStatus !== 'verified') {
            throw new \DomainException('Only verified registrations can be checked in.');
        }
    }

    private function ensureValidDay(int $day): void
    {
        if (! in_array($day, Attendance::SEMINAR_DAYS, true)) {
            throw new \DomainException('The seminar runs for three days.');
        }
    }
}
