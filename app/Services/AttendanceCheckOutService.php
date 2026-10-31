<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use App\Models\User;

class AttendanceCheckOutService
{
    /**
     * Stamp the check-out on an existing attendance row.
     *
     * Idempotent by intent: a second scan must not overwrite the original time
     * or the staff member who recorded it, because check-out is evidence of
     * when the participant left.
     */
    public function checkOut(Attendance $attendance, User $staff): Attendance
    {
        if ($attendance->checked_out_at === null) {
            $attendance->update([
                'checked_out_at' => now(),
                'checked_out_by' => $staff->id,
            ]);
        }

        return $attendance;
    }

    public function checkOutSeminar(SeminarRegistration $registration, User $staff): bool
    {
        $attendance = Attendance::query()
            ->where('seminar_registration_id', $registration->id)
            // A hands-on row also carries seminar_registration_id, so the null
            // hands-on id is what identifies the seminar attendance itself.
            ->whereNull('hands_on_registration_id')
            ->where('activity_type', 'seminar')
            ->first();

        if (! $attendance) {
            return false;
        }

        $this->checkOut($attendance, $staff);

        return true;
    }

    public function checkOutHandsOn(HandsOnRegistration $registration, User $staff): bool
    {
        $attendance = Attendance::query()
            ->where('hands_on_registration_id', $registration->id)
            ->where('activity_type', 'hands_on')
            ->first();

        if (! $attendance) {
            return false;
        }

        $this->checkOut($attendance, $staff);

        return true;
    }
}
