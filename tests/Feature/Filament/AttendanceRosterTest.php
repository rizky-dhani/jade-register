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
function callRosterAction(string $page, string $action, $record): void
{
    livewire($page)->instance()->getTable()->getAction($action)->call(['record' => $record]);
}

it('lists every verified seminar participant, checked in or not', function () {
    $checkedIn = SeminarRegistration::factory()->verified()->create(['name_license' => 'Ayu Kartika']);
    $notYet = SeminarRegistration::factory()->verified()->create(['name_license' => 'Budi Santoso']);

    Attendance::factory()->create([
        'seminar_registration_id' => $checkedIn->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $this->user->id,
    ]);

    livewire(ListSeminarAttendances::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$checkedIn, $notYet]);
});

it('leaves unpaid seminar registrations off the desk roster', function () {
    $verified = SeminarRegistration::factory()->verified()->create();
    $pending = SeminarRegistration::factory()->pending()->create();
    $rejected = SeminarRegistration::factory()->rejected()->create();

    livewire(ListSeminarAttendances::class)
        ->assertCanSeeTableRecords([$verified])
        ->assertCanNotSeeTableRecords([$pending, $rejected]);
});

it('lists every verified hands-on session registration, checked in or not', function () {
    $handsOn = HandsOn::factory()->create();
    $seminar = SeminarRegistration::factory()->verified()->create();

    $checkedIn = HandsOnRegistration::factory()->verified()->create([
        'seminar_registration_id' => $seminar->id,
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'combined',
    ]);
    $notYet = HandsOnRegistration::factory()->verified()->create([
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'hands_on',
    ]);

    Attendance::factory()->handsOn()->create([
        'seminar_registration_id' => $seminar->id,
        'hands_on_registration_id' => $checkedIn->id,
        'checked_in_by' => $this->user->id,
    ]);

    livewire(ListHandsOnAttendances::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$checkedIn, $notYet]);
});

it('filters the seminar roster down to participants who have not checked in', function () {
    $checkedIn = SeminarRegistration::factory()->verified()->create();
    $notYet = SeminarRegistration::factory()->verified()->create();

    Attendance::factory()->create([
        'seminar_registration_id' => $checkedIn->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $this->user->id,
    ]);

    livewire(ListSeminarAttendances::class)
        ->filterTable('is_checked_in', 'not_checked_in')
        ->assertCanSeeTableRecords([$notYet])
        ->assertCanNotSeeTableRecords([$checkedIn]);
});

it('filters the hands-on roster down to sessions that have not checked in', function () {
    $handsOn = HandsOn::factory()->create();

    $checkedIn = HandsOnRegistration::factory()->verified()->create([
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'hands_on',
    ]);
    $notYet = HandsOnRegistration::factory()->verified()->create([
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'hands_on',
    ]);

    Attendance::factory()->handsOn()->create([
        'seminar_registration_id' => null,
        'hands_on_registration_id' => $checkedIn->id,
        'checked_in_by' => $this->user->id,
    ]);

    livewire(ListHandsOnAttendances::class)
        ->filterTable('is_checked_in', 'not_checked_in')
        ->assertCanSeeTableRecords([$notYet])
        ->assertCanNotSeeTableRecords([$checkedIn]);
});

it('checks a seminar participant in for a single day from their roster row', function () {
    $registration = SeminarRegistration::factory()->verified()->create();

    livewire(ListSeminarAttendances::class)
        ->assertTableActionVisible('checkInDay1', $registration)
        ->assertTableActionVisible('checkInDay2', $registration)
        ->assertTableActionVisible('checkInDay3', $registration);

    callRosterAction(ListSeminarAttendances::class, 'checkInDay2', $registration);

    $attendance = Attendance::query()
        ->where('seminar_registration_id', $registration->id)
        ->where('activity_type', 'seminar')
        ->sole();

    expect($attendance->day)->toBe(2)
        ->and($attendance->checked_in_at)->not->toBeNull()
        ->and($attendance->checked_in_by)->toBe($this->user->id)
        ->and($attendance->hands_on_registration_id)->toBeNull();
});

it('accumulates one seminar attendance row per day checked in', function () {
    $registration = SeminarRegistration::factory()->verified()->create();

    callRosterAction(ListSeminarAttendances::class, 'checkInDay1', $registration);
    callRosterAction(ListSeminarAttendances::class, 'checkInDay3', $registration);

    $days = Attendance::query()
        ->where('seminar_registration_id', $registration->id)
        ->where('activity_type', 'seminar')
        ->orderBy('day')
        ->pluck('day')
        ->all();

    expect($days)->toBe([1, 3]);
});

it('checks a hands-on participant in from their own roster row', function () {
    $handsOn = HandsOn::factory()->create();
    $seminar = SeminarRegistration::factory()->verified()->create();
    $registration = HandsOnRegistration::factory()->verified()->create([
        'seminar_registration_id' => $seminar->id,
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'combined',
    ]);

    livewire(ListHandsOnAttendances::class)
        ->assertTableActionVisible('checkIn', $registration);

    callRosterAction(ListHandsOnAttendances::class, 'checkIn', $registration);

    $attendance = Attendance::query()
        ->where('hands_on_registration_id', $registration->id)
        ->where('activity_type', 'hands_on')
        ->sole();

    expect($attendance->checked_in_at)->not->toBeNull()
        ->and($attendance->seminar_registration_id)->toBe($seminar->id);
});

it('hides only the checked-in day, and offers check-out once any day is in', function () {
    $registration = SeminarRegistration::factory()->verified()->create();

    Attendance::factory()->day(2)->create([
        'seminar_registration_id' => $registration->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $this->user->id,
    ]);

    livewire(ListSeminarAttendances::class)
        ->assertTableActionHidden('checkInDay2', $registration)
        ->assertTableActionVisible('checkInDay1', $registration)
        ->assertTableActionVisible('checkInDay3', $registration)
        ->assertTableActionVisible('checkOut', $registration);
});

/**
 * The attendance pages are gated on `view attendances`, which Participant does
 * not hold — so a participant never reaches the roster at all, rather than
 * reaching it with the buttons hidden.
 */
it('keeps participants off the attendance roster entirely', function () {
    $participant = User::factory()->create();
    $participant->assignRole('Participant');
    actingAs($participant);

    expect(AttendanceResource::canViewAny())->toBeFalse();
});

it('keeps the roster check-in away from a role without the check-in permission', function () {
    $viewer = User::factory()->create();
    $viewer->assignRole('Super Admin');
    $viewer->syncRoles([]);
    $viewer->givePermissionTo('view attendances');
    actingAs($viewer);

    $registration = SeminarRegistration::factory()->verified()->create();

    livewire(ListSeminarAttendances::class)
        ->assertOk()
        ->assertTableActionHidden('checkInDay1', $registration)
        ->assertTableActionHidden('checkInDay2', $registration)
        ->assertTableActionHidden('checkInDay3', $registration);
});
