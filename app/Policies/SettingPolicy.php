<?php

namespace App\Policies;

use App\Models\User;

class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function view(User $user, $setting): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function update(User $user, $setting): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function delete(User $user, $setting): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function restore(User $user, $setting): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function forceDelete(User $user, $setting): bool
    {
        return $user->hasRole('Super Admin');
    }
}
