<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use App\Models\User;

/**
 * Cancels a check-in the desk recorded by mistake.
 *
 * The row is kept and marked cancelled rather than deleted: the desk needs a
 * record of the correction, and a cancelled row leaves the day (or session)
 * open to be checked in again.
 */
class AttendanceCancelService
{
    /**
     * Cancel the seminar check-in for one event day.
     *
     * Returns false when there is no active check-in for that day, so the action
     * can report it instead of silently doing nothing.
     */
    public function cancelSeminarDay(SeminarRegistration $registration, User $staff, int $day): bool
    {
        $attendance = Attendance::query()
            ->where('seminar_registration_id', $registration->id)
            ->whereNull('hands_on_registration_id')
            ->where('activity_type', 'seminar')
            ->where('day', $day)
            ->whereNull('cancelled_at')
            ->first();

        if (! $attendance) {
            return false;
        }

        $this->cancel($attendance, $staff);

        return true;
    }

    /**
     * Undo a check-out for one event day, leaving the day checked in and open
     * again.
     *
     * The check-out stamp is cleared rather than kept: unlike a cancelled
     * check-in, there is no column that records a cancelled check-out, and a
     * stale `checked_out_at` would keep the day closed.
     */
    public function cancelCheckOutSeminar(SeminarRegistration $registration, int $day): bool
    {
        $attendance = Attendance::query()
            ->where('seminar_registration_id', $registration->id)
            ->whereNull('hands_on_registration_id')
            ->where('activity_type', 'seminar')
            ->where('day', $day)
            ->whereNull('cancelled_at')
            ->whereNotNull('checked_out_at')
            ->first();

        if (! $attendance) {
            return false;
        }

        $attendance->update([
            'checked_out_at' => null,
            'checked_out_by' => null,
        ]);

        return true;
    }

    /**
     * Cancel the check-in for a hands-on session.
     */
    public function cancelHandsOn(HandsOnRegistration $registration, User $staff): bool
    {
        $attendance = Attendance::query()
            ->where('hands_on_registration_id', $registration->id)
            ->where('activity_type', 'hands_on')
            ->whereNull('cancelled_at')
            ->first();

        if (! $attendance) {
            return false;
        }

        $this->cancel($attendance, $staff);

        return true;
    }

    /**
     * Idempotent by intent: a second cancel must not overwrite the staff member
     * or time that recorded the correction, for the same reason check-out never
     * overwrites its own stamp.
     */
    public function cancel(Attendance $attendance, User $staff): Attendance
    {
        if ($attendance->cancelled_at === null) {
            $attendance->update([
                'cancelled_at' => now(),
                'cancelled_by' => $staff->id,
            ]);
        }

        return $attendance;
    }
}
