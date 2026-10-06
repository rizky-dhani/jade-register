<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('creates all six digital workshop permissions', function () {
    $count = Permission::where('name', 'like', '% digital workshops')->count();

    expect($count)->toBe(6);
});

test('grants digital workshop permissions to Super Admin', function () {
    $role = Role::findByName('Super Admin');

    foreach (['view', 'create', 'update', 'delete', 'restore', 'force delete'] as $action) {
        expect($role->hasPermissionTo("{$action} digital workshops"))->toBeTrue();
    }
});

test('grants view and update digital workshop permissions to Admin', function () {
    $role = Role::findByName('Admin');

    expect($role->hasPermissionTo('view digital workshops'))->toBeTrue();
    expect($role->hasPermissionTo('update digital workshops'))->toBeTrue();
    expect($role->hasPermissionTo('force delete digital workshops'))->toBeFalse();
});

test('assigns the permissions to a user acting as Super Admin', function () {
    $user = User::factory()->create();
    $user->assignRole('Super Admin');

    // Gate::before grants Super Admin every check, so can() can never fail here.
    expect($user->hasPermissionTo('view digital workshops'))->toBeTrue();
    expect($user->hasPermissionTo('force delete digital workshops'))->toBeTrue();
});
