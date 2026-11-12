<?php

namespace App\Policies;

use App\Models\LunchRedemption;
use App\Models\User;

/**
 * Lunch redemptions are an audit trail written by the desk, not a record type
 * the panel edits. The page is read-only, so only the reading abilities exist.
 */
class LunchRedemptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view lunch attendances');
    }

    public function view(User $user, LunchRedemption $lunchRedemption): bool
    {
        return $user->can('view lunch attendances');
    }
}
