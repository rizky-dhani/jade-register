<?php

namespace App\Policies;

use App\Models\DigitalWorkshopRegistration;
use App\Models\User;

class DigitalWorkshopRegistrationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view digital workshop registrations');
    }

    public function view(User $user, DigitalWorkshopRegistration $digitalWorkshopRegistration): bool
    {
        return $user->can('view digital workshop registrations');
    }

    public function create(User $user): bool
    {
        return $user->can('create digital workshop registrations');
    }

    public function update(User $user, DigitalWorkshopRegistration $digitalWorkshopRegistration): bool
    {
        return $user->can('update digital workshop registrations');
    }

    public function delete(User $user, DigitalWorkshopRegistration $digitalWorkshopRegistration): bool
    {
        return $user->can('delete digital workshop registrations');
    }

    public function restore(User $user, DigitalWorkshopRegistration $digitalWorkshopRegistration): bool
    {
        return $user->can('restore digital workshop registrations');
    }

    public function forceDelete(User $user, DigitalWorkshopRegistration $digitalWorkshopRegistration): bool
    {
        return $user->can('force delete digital workshop registrations');
    }
}
