<?php

namespace App\Policies;

use App\Models\DigitalWorkshop;
use App\Models\User;

class DigitalWorkshopPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view digital workshops');
    }

    public function view(User $user, DigitalWorkshop $digitalWorkshop): bool
    {
        return $user->can('view digital workshops');
    }

    public function create(User $user): bool
    {
        return $user->can('create digital workshops');
    }

    public function update(User $user, DigitalWorkshop $digitalWorkshop): bool
    {
        return $user->can('update digital workshops');
    }

    public function delete(User $user, DigitalWorkshop $digitalWorkshop): bool
    {
        return $user->can('delete digital workshops');
    }

    public function restore(User $user, DigitalWorkshop $digitalWorkshop): bool
    {
        return $user->can('restore digital workshops');
    }

    public function forceDelete(User $user, DigitalWorkshop $digitalWorkshop): bool
    {
        return $user->can('force delete digital workshops');
    }
}
