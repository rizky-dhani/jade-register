<?php

namespace App\Policies;

use App\Models\HandsOnRegistration;
use App\Models\User;

class HandsOnRegistrationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view hands on registrations');
    }

    public function view(User $user, HandsOnRegistration $handsOnRegistration): bool
    {
        return $user->can('view hands on registrations');
    }

    public function create(User $user): bool
    {
        return $user->can('create hands on registrations');
    }

    public function update(User $user, HandsOnRegistration $handsOnRegistration): bool
    {
        return $user->can('update hands on registrations');
    }

    public function delete(User $user, HandsOnRegistration $handsOnRegistration): bool
    {
        return $user->can('delete hands on registrations');
    }

    public function restore(User $user, HandsOnRegistration $handsOnRegistration): bool
    {
        return $user->can('restore hands on registrations');
    }

    public function forceDelete(User $user, HandsOnRegistration $handsOnRegistration): bool
    {
        return $user->can('force delete hands on registrations');
    }
}
