<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;

class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage settings');
    }

    public function view(User $user, Setting $setting): bool
    {
        return $user->can('manage settings');
    }

    public function create(User $user): bool
    {
        return $user->can('manage settings');
    }

    public function update(User $user, Setting $setting): bool
    {
        return $user->can('manage settings');
    }

    public function delete(User $user, Setting $setting): bool
    {
        return $user->can('manage settings');
    }

    public function restore(User $user, Setting $setting): bool
    {
        return $user->can('manage settings');
    }

    public function forceDelete(User $user, Setting $setting): bool
    {
        return $user->can('manage settings');
    }
}
