<?php

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Resources\Attendances\Pages\ListHandsOnAttendances;
use App\Filament\Resources\Attendances\Pages\ListSeminarAttendances;
use App\Models\Attendance;
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
function callCheckOutAction(string $page, $record): void
{
    livewire($page)->instance()->getTable()->getAction('checkOut')->call(['record' => $record]);
}

it('checks out a seminar participant from the seminar roster', function () {
    $registration = SeminarRegistration::factory()->verified()->create();
    $attendance = Attendance::factory()->create([
        'seminar_registration_id' => $registration->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $this->user->id,
    ]);

    callCheckOutAction(ListSeminarAttendances::class, $registration);

    $attendance->refresh();

    expect($attendance->checked_out_at)->not->toBeNull()
        ->and($attendance->checked_out_by)->toBe($this->user->id);
});

it('checks out a hands-on participant from the hands-on roster', function () {
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

    callCheckOutAction(ListHandsOnAttendances::class, $handsOnReg);

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

    livewire(ListSeminarAttendances::class)
        ->assertTableActionVisible('checkOut', $stillIn)
        ->assertTableActionHidden('checkOut', $alreadyOut);
});

it('keeps the original check-out when the action runs twice', function () {
    $registration = SeminarRegistration::factory()->verified()->create();
    $attendance = Attendance::factory()->create([
        'seminar_registration_id' => $registration->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $this->user->id,
    ]);

    callCheckOutAction(ListSeminarAttendances::class, $registration);
    $attendance->refresh();
    $firstCheckOutAt = $attendance->checked_out_at;
    $firstCheckedOutBy = $attendance->checked_out_by;

    $otherStaff = User::factory()->create();
    $otherStaff->assignRole('Super Admin');
    actingAs($otherStaff);

    callCheckOutAction(ListSeminarAttendances::class, $registration);
    $attendance->refresh();

    expect($attendance->checked_out_at->equalTo($firstCheckOutAt))->toBeTrue()
        ->and($attendance->checked_out_by)->toBe($firstCheckedOutBy);
});

/**
 * The Admin desk role has no `update attendances` permission, so gating the
 * action on it hid check-out from the very people who scan at the desk.
 */
it('lets a desk admin check a participant out from the seminar roster', function () {
    $desk = User::factory()->create();
    $desk->assignRole('Admin');
    actingAs($desk);

    $registration = SeminarRegistration::factory()->verified()->create();
    $attendance = Attendance::factory()->create([
        'seminar_registration_id' => $registration->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $desk->id,
    ]);

    livewire(ListSeminarAttendances::class)
        ->assertTableActionVisible('checkOut', $registration);

    callCheckOutAction(ListSeminarAttendances::class, $registration);

    $attendance->refresh();

    expect($attendance->checked_out_at)->not->toBeNull()
        ->and($attendance->checked_out_by)->toBe($desk->id);
});

it('keeps participants off the attendance roster entirely', function () {
    $participant = User::factory()->create();
    $participant->assignRole('Participant');
    actingAs($participant);

    expect(AttendanceResource::canViewAny())->toBeFalse();
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

    livewire(ListSeminarAttendances::class)
        ->assertOk()
        ->assertTableActionHidden('checkOut', $registration);
});
