<?php

namespace App\Policies;

use App\Models\SeminarRegistration;
use App\Models\User;

class SeminarRegistrationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view seminar registrations');
    }

    public function view(User $user, SeminarRegistration $seminarRegistration): bool
    {
        return $user->can('view seminar registrations');
    }

    public function create(User $user): bool
    {
        return $user->can('create seminar registrations');
    }

    public function update(User $user, SeminarRegistration $seminarRegistration): bool
    {
        return $user->can('update seminar registrations');
    }

    public function delete(User $user, SeminarRegistration $seminarRegistration): bool
    {
        return $user->can('delete seminar registrations');
    }

    public function restore(User $user, SeminarRegistration $seminarRegistration): bool
    {
        return $user->can('restore seminar registrations');
    }

    public function forceDelete(User $user, SeminarRegistration $seminarRegistration): bool
    {
        return $user->can('force delete seminar registrations');
    }
}
