<?php

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Resources\Attendances\Pages\ListAttendanceRoster;
use App\Models\Attendance;
use App\Models\AttendanceRoster;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Filament\Actions\ActionGroup;
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
function callRosterAction(string $action, $record): void
{
    livewire(ListAttendanceRoster::class)->instance()->getTable()->getAction($action)->call(['record' => $record]);
}

function rosterRowFor(SeminarRegistration $registration)
{
    return AttendanceRoster::where('participant_key', 'seminar-'.$registration->id)->sole();
}

it('lists verified seminar and hands-on participants in one table', function () {
    $seminar = SeminarRegistration::factory()->verified()->create(['name_license' => 'Ayu Kartika']);

    $handsOn = HandsOn::factory()->create(['name' => 'Dental Implant Workshop']);
    $standalone = HandsOnRegistration::factory()->verified()->create([
        'seminar_registration_id' => null,
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'hands_on',
        'name_license' => 'Budi Santoso',
    ]);

    livewire(ListAttendanceRoster::class)
        ->assertOk()
        ->assertCanSeeTableRecords(AttendanceRoster::all())
        ->assertSee('Ayu Kartika')
        ->assertSee('Budi Santoso');
});

it('leaves unpaid registrations off the roster', function () {
    $verified = SeminarRegistration::factory()->verified()->create();
    $pending = SeminarRegistration::factory()->pending()->create();

    livewire(ListAttendanceRoster::class)
        ->assertCanSeeTableRecords([rosterRowFor($verified)])
        ->assertCanNotSeeTableRecords(AttendanceRoster::where('seminar_registration_id', $pending->id)->get());
});

it('filters the roster by activity type', function () {
    $seminar = SeminarRegistration::factory()->verified()->create();

    $handsOn = HandsOn::factory()->create();
    $standalone = HandsOnRegistration::factory()->verified()->create([
        'seminar_registration_id' => null,
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'hands_on',
    ]);

    $seminarRow = rosterRowFor($seminar);
    $handsOnRow = AttendanceRoster::where('participant_key', 'hands_on-'.$standalone->id)->sole();

    livewire(ListAttendanceRoster::class)
        ->filterTable('activity_type', 'seminar')
        ->assertCanSeeTableRecords([$seminarRow])
        ->assertCanNotSeeTableRecords([$handsOnRow])
        ->filterTable('activity_type', 'hands_on')
        ->assertCanSeeTableRecords([$handsOnRow])
        ->assertCanNotSeeTableRecords([$seminarRow]);
});

it('does not list a linked hands-on registration as its own row', function () {
    $seminar = SeminarRegistration::factory()->verified()->create();
    $handsOn = HandsOn::factory()->create();

    $linked = HandsOnRegistration::factory()->verified()->create([
        'seminar_registration_id' => $seminar->id,
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'combined',
    ]);

    // The participant is one row (the seminar registration); the hands-on
    // session shows inside it rather than doubling the row count.
    expect(AttendanceRoster::count())->toBe(1)
        ->and(AttendanceRoster::where('participant_key', 'hands_on-'.$linked->id)->exists())->toBeFalse();
});

it('checks a seminar day in from the roster row', function () {
    $registration = SeminarRegistration::factory()->verified()->create();
    $row = rosterRowFor($registration);

    livewire(ListAttendanceRoster::class)
        ->assertTableActionVisible('checkInDay2', $row);

    callRosterAction('checkInDay2', $row);

    $attendance = Attendance::query()
        ->where('seminar_registration_id', $registration->id)
        ->where('activity_type', 'seminar')
        ->sole();

    expect($attendance->day)->toBe(2)
        ->and($attendance->checked_in_by)->toBe($this->user->id);
});

it('checks in a standalone hands-on participant from the roster row', function () {
    $handsOn = HandsOn::factory()->create();
    $registration = HandsOnRegistration::factory()->verified()->create([
        'seminar_registration_id' => null,
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'hands_on',
    ]);

    $row = AttendanceRoster::where('participant_key', 'hands_on-'.$registration->id)->sole();

    callRosterAction('checkInHandsOn', $row);

    expect(Attendance::query()
        ->where('hands_on_registration_id', $registration->id)
        ->where('activity_type', 'hands_on')
        ->exists())->toBeTrue();
});

it('hides the day check-in actions once that day is in', function () {
    $registration = SeminarRegistration::factory()->verified()->create();

    Attendance::factory()->day(1)->create([
        'seminar_registration_id' => $registration->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $this->user->id,
    ]);

    $row = rosterRowFor($registration);

    livewire(ListAttendanceRoster::class)
        ->assertTableActionHidden('checkInDay1', $row)
        ->assertTableActionVisible('checkInDay2', $row)
        ->assertTableActionVisible('checkInDay3', $row)
        ->assertTableActionVisible('checkOut', $row);
});

it('checks a participant out from the roster row', function () {
    $registration = SeminarRegistration::factory()->verified()->create();
    $attendance = Attendance::factory()->create([
        'seminar_registration_id' => $registration->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $this->user->id,
    ]);

    callRosterAction('checkOut', rosterRowFor($registration));

    expect($attendance->refresh()->checked_out_at)->not->toBeNull()
        ->and($attendance->checked_out_by)->toBe($this->user->id);
});

it('keeps participants off the attendance roster entirely', function () {
    $participant = User::factory()->create();
    $participant->assignRole('Participant');
    actingAs($participant);

    expect(AttendanceResource::canViewAny())->toBeFalse();
});

it('hides the roster actions from a role without the check-in permission', function () {
    $viewer = User::factory()->create();
    $viewer->syncRoles([]);
    $viewer->givePermissionTo('view attendances');
    actingAs($viewer);

    $registration = SeminarRegistration::factory()->verified()->create();
    $row = rosterRowFor($registration);

    livewire(ListAttendanceRoster::class)
        ->assertOk()
        ->assertTableActionHidden('checkInDay1', $row)
        ->assertTableActionHidden('checkInHandsOn', $row)
        ->assertTableActionHidden('checkOut', $row);
});

it('searches the roster by participant name and registration code', function () {
    $ayu = SeminarRegistration::factory()->verified()->create(['name_license' => 'Ayu Kartika']);
    $budi = SeminarRegistration::factory()->verified()->create(['name_license' => 'Budi Santoso']);

    livewire(ListAttendanceRoster::class)
        ->searchTable('Ayu Kartika')
        ->assertCanSeeTableRecords([rosterRowFor($ayu)])
        ->assertCanNotSeeTableRecords([rosterRowFor($budi)]);
});

it('collapses the per-day check-in, cancel, and check-out actions into one dropdown each', function () {
    $registration = SeminarRegistration::factory()->verified()->create();
    $row = rosterRowFor($registration);

    $groups = collect(livewire(ListAttendanceRoster::class)->instance()->getTable()->getRecordActions())
        ->filter(fn ($action): bool => $action instanceof ActionGroup)
        ->values();

    expect($groups)->toHaveCount(3)
        ->and($groups[0]->getFlatActions())->toHaveKeys(['checkInDay1', 'checkInDay2', 'checkInDay3'])
        ->and($groups[1]->getFlatActions())->toHaveKeys(['cancelCheckInDay1', 'cancelCheckInDay2', 'cancelCheckInDay3'])
        ->and($groups[2]->getFlatActions())->toHaveKeys(['checkOutDay1', 'checkOutDay2', 'checkOutDay3'])
        ->and($groups[0]->record($row)->isHidden())->toBeFalse()
        ->and($groups[1]->record($row)->isHidden())->toBeTrue()
        ->and($groups[2]->record($row)->isHidden())->toBeTrue();
});

it('hides the check-in dropdown once every day is checked in', function () {
    $registration = SeminarRegistration::factory()->verified()->create();

    foreach (Attendance::SEMINAR_DAYS as $day) {
        Attendance::factory()->day($day)->create([
            'seminar_registration_id' => $registration->id,
            'activity_type' => 'seminar',
            'checked_in_by' => $this->user->id,
        ]);
    }

    $row = rosterRowFor($registration);

    $groups = collect(livewire(ListAttendanceRoster::class)->instance()->getTable()->getRecordActions())
        ->filter(fn ($action): bool => $action instanceof ActionGroup)
        ->values();

    expect($groups[0]->record($row)->isHidden())->toBeTrue()
        ->and($groups[1]->record($row)->isHidden())->toBeFalse();
});
