<?php

namespace App\Policies;

use App\Models\HandsOn;
use App\Models\User;

class HandsOnPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view hands ons');
    }

    public function view(User $user, HandsOn $handsOn): bool
    {
        return $user->can('view hands ons');
    }

    public function create(User $user): bool
    {
        return $user->can('create hands ons');
    }

    public function update(User $user, HandsOn $handsOn): bool
    {
        return $user->can('update hands ons');
    }

    public function delete(User $user, HandsOn $handsOn): bool
    {
        return $user->can('delete hands ons');
    }

    public function restore(User $user, HandsOn $handsOn): bool
    {
        return $user->can('restore hands ons');
    }

    public function forceDelete(User $user, HandsOn $handsOn): bool
    {
        return $user->can('force delete hands ons');
    }
}
