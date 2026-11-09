<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    /**
     * Reading the desk rosters (seminar, hands-on, visitor).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view attendances');
    }

    public function view(User $user, Attendance $attendance): bool
    {
        return $user->can('view attendances');
    }

    /**
     * Checking a participant in — the QR scan and the roster's per-day buttons.
     * This is the desk's main job, so it is a permission of its own rather than
     * being folded into `update attendances`.
     */
    public function checkIn(User $user): bool
    {
        return $user->can('check in participants');
    }

    /**
     * Checking a participant out. Separate from check-in because the two are
     * different moments at the desk and a role may hold only one.
     */
    public function checkOut(User $user): bool
    {
        return $user->can('check out participants');
    }

    public function create(User $user): bool
    {
        return $user->can('create attendances');
    }

    public function update(User $user, Attendance $attendance): bool
    {
        return $user->can('update attendances');
    }

    public function delete(User $user, Attendance $attendance): bool
    {
        return $user->can('delete attendances');
    }
}
