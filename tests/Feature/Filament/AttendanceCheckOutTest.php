<?php

use App\Filament\Resources\Attendances\Pages\ListAttendanceRoster;
use App\Models\Attendance;
use App\Models\AttendanceRoster;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');
    actingAs($this->user);
});

/**
 * callTableAction routes its data through fillForm(), which silently no-ops in
 * this app's harness, so the closure is invoked directly.
 *
 * @see .ai/rules/filament.md
 */
function callCheckOutAction($row): void
{
    livewire(ListAttendanceRoster::class)->instance()->getTable()->getAction('checkOut')->call(['record' => $row]);
}

function rosterRowForSeminar(SeminarRegistration $registration)
{
    return AttendanceRoster::where('participant_key', 'seminar-'.$registration->id)->sole();
}

it('checks out a seminar participant from the roster', function () {
    $registration = SeminarRegistration::factory()->verified()->create();
    $attendance = Attendance::factory()->create([
        'seminar_registration_id' => $registration->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $this->user->id,
    ]);

    callCheckOutAction(rosterRowForSeminar($registration));

    $attendance->refresh();

    expect($attendance->checked_out_at)->not->toBeNull()
        ->and($attendance->checked_out_by)->toBe($this->user->id);
});

it('checks out a standalone hands-on participant from the roster', function () {
    $handsOn = HandsOn::factory()->create();
    $handsOnReg = HandsOnRegistration::factory()->verified()->create([
        'seminar_registration_id' => null,
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'hands_on',
    ]);

    $attendance = Attendance::factory()->handsOn()->create([
        'seminar_registration_id' => null,
        'hands_on_registration_id' => $handsOnReg->id,
        'checked_in_by' => $this->user->id,
    ]);

    $row = AttendanceRoster::where('participant_key', 'hands_on-'.$handsOnReg->id)->sole();

    callCheckOutAction($row);

    expect($attendance->refresh()->checked_out_at)->not->toBeNull();
});

it('offers the check-out action only while the participant is still checked in', function () {
    $stillIn = SeminarRegistration::factory()->verified()->create();
    $alreadyOut = SeminarRegistration::factory()->verified()->create();

    Attendance::factory()->create([
        'seminar_registration_id' => $stillIn->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $this->user->id,
    ]);

    Attendance::factory()->checkedOut()->create([
        'seminar_registration_id' => $alreadyOut->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $this->user->id,
    ]);

    livewire(ListAttendanceRoster::class)
        ->assertTableActionVisible('checkOut', rosterRowForSeminar($stillIn))
        ->assertTableActionHidden('checkOut', rosterRowForSeminar($alreadyOut));
});

it('keeps the original check-out when the action runs twice', function () {
    $registration = SeminarRegistration::factory()->verified()->create();
    $attendance = Attendance::factory()->create([
        'seminar_registration_id' => $registration->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $this->user->id,
    ]);

    $row = rosterRowForSeminar($registration);

    callCheckOutAction($row);
    $attendance->refresh();
    $firstCheckOutAt = $attendance->checked_out_at;
    $firstCheckedOutBy = $attendance->checked_out_by;

    $otherStaff = User::factory()->create();
    $otherStaff->assignRole('Super Admin');
    actingAs($otherStaff);

    callCheckOutAction($row);
    $attendance->refresh();

    expect($attendance->checked_out_at->equalTo($firstCheckOutAt))->toBeTrue()
        ->and($attendance->checked_out_by)->toBe($firstCheckedOutBy);
});

/**
 * The Admin desk role has no `update attendances` permission, so gating the
 * action on it hid check-out from the very people who scan at the desk.
 */
it('lets a desk admin check a participant out from the roster', function () {
    $desk = User::factory()->create();
    $desk->assignRole('Admin');
    actingAs($desk);

    $registration = SeminarRegistration::factory()->verified()->create();
    $attendance = Attendance::factory()->create([
        'seminar_registration_id' => $registration->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $desk->id,
    ]);

    $row = rosterRowForSeminar($registration);

    livewire(ListAttendanceRoster::class)
        ->assertTableActionVisible('checkOut', $row);

    callCheckOutAction($row);

    $attendance->refresh();

    expect($attendance->checked_out_at)->not->toBeNull()
        ->and($attendance->checked_out_by)->toBe($desk->id);
});

it('keeps the check-out action away from a role without the check-out permission', function () {
    $viewer = User::factory()->create();
    $viewer->syncRoles([]);
    $viewer->givePermissionTo(['view attendances', 'check in participants']);
    actingAs($viewer);

    $registration = SeminarRegistration::factory()->verified()->create();
    Attendance::factory()->create([
        'seminar_registration_id' => $registration->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $this->user->id,
    ]);

    livewire(ListAttendanceRoster::class)
        ->assertOk()
        ->assertTableActionHidden('checkOut', rosterRowForSeminar($registration));
});
