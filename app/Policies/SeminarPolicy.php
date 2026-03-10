<?php

namespace App\Policies;

use App\Models\Seminar;
use App\Models\User;

class SeminarPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view seminars');
    }

    public function view(User $user, Seminar $seminar): bool
    {
        return $user->can('view seminars');
    }

    public function create(User $user): bool
    {
        return $user->can('create seminars');
    }

    public function update(User $user, Seminar $seminar): bool
    {
        return $user->can('update seminars');
    }

    public function delete(User $user, Seminar $seminar): bool
    {
        return $user->can('delete seminars');
    }

    public function restore(User $user, Seminar $seminar): bool
    {
        return $user->can('restore seminars');
    }

    public function forceDelete(User $user, Seminar $seminar): bool
    {
        return $user->can('force delete seminars');
    }
}
