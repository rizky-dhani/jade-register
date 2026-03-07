<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visitor;

class VisitorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view visitors');
    }

    public function view(User $user, Visitor $visitor): bool
    {
        return $user->can('view visitors');
    }

    public function create(User $user): bool
    {
        return $user->can('create visitors');
    }

    public function update(User $user, Visitor $visitor): bool
    {
        return $user->can('update visitors');
    }

    public function delete(User $user, Visitor $visitor): bool
    {
        return $user->can('delete visitors');
    }

    public function restore(User $user, Visitor $visitor): bool
    {
        return $user->can('restore visitors');
    }

    public function forceDelete(User $user, Visitor $visitor): bool
    {
        return $user->can('force delete visitors');
    }
}
