<?php

use App\Models\Attendance;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function userWithRole(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);
    actingAs($user);

    return $user;
}

/**
 * `attendances` used to have no policy at all, so Filament's fallback allowed
 * every logged-in user to read the desk rosters. These pin the replacement.
 */
it('grants the desk role and super admin the attendance permissions', function () {
    foreach (['Admin', 'Super Admin'] as $role) {
        $user = userWithRole($role);

        expect($user->can('view attendances'))->toBeTrue()
            ->and($user->can('checkIn', Attendance::class))->toBeTrue()
            ->and($user->can('checkOut', Attendance::class))->toBeTrue();
    }
});

it('does not grant attendance permissions to participants or judges', function () {
    foreach (['Participant', 'Judge'] as $role) {
        $user = userWithRole($role);

        expect($user->can('view attendances'))->toBeFalse()
            ->and($user->can('checkIn', Attendance::class))->toBeFalse()
            ->and($user->can('checkOut', Attendance::class))->toBeFalse()
            ->and($user->can('viewAny', Attendance::class))->toBeFalse();
    }
});

it('keeps check-in separate from the record-write permissions', function () {
    $desk = userWithRole('Admin');

    // The desk checks people in without being able to edit or delete the rows.
    expect($desk->can('checkIn', Attendance::class))->toBeTrue()
        ->and($desk->can('update', new Attendance))->toBeFalse()
        ->and($desk->can('delete', new Attendance))->toBeFalse();
});

it('lets a role hold only check-in, not check-out', function () {
    $user = User::factory()->create();
    $user->syncRoles([]);
    $user->givePermissionTo(['view attendances', 'check in participants']);
    actingAs($user);

    expect($user->can('checkIn', Attendance::class))->toBeTrue()
        ->and($user->can('checkOut', Attendance::class))->toBeFalse();
});
