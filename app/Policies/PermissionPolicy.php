<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Permission;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view permissions');
    }

    public function view(User $user, Permission $permission): bool
    {
        return $user->can('view permissions');
    }

    public function create(User $user): bool
    {
        return $user->can('create permissions');
    }

    public function update(User $user, Permission $permission): bool
    {
        return $user->can('update permissions');
    }

    public function delete(User $user, Permission $permission): bool
    {
        return $user->can('delete permissions');
    }

    public function restore(User $user, Permission $permission): bool
    {
        return $user->can('restore permissions');
    }

    public function forceDelete(User $user, Permission $permission): bool
    {
        return $user->can('force delete permissions');
    }
}
