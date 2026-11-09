<?php

use App\Filament\Resources\Attendances\Pages\ListAttendanceRoster;
use App\Models\Attendance;
use App\Models\AttendanceRoster;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\Seminar;
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

function callCancelAction(string $action, $record): void
{
    livewire(ListAttendanceRoster::class)->instance()->getTable()->getAction($action)->call(['record' => $record]);
}

function seminarRow(SeminarRegistration $registration)
{
    return AttendanceRoster::where('participant_key', 'seminar-'.$registration->id)->sole();
}

it('cancels a day check-in without deleting the row', function () {
    $registration = SeminarRegistration::factory()->verified()->create();
    $attendance = Attendance::factory()->day(2)->create([
        'seminar_registration_id' => $registration->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $this->user->id,
    ]);

    callCancelAction('cancelCheckInDay2', seminarRow($registration));

    $attendance->refresh();

    expect($attendance->exists)->toBeTrue()
        ->and($attendance->cancelled_at)->not->toBeNull()
        ->and($attendance->cancelled_by)->toBe($this->user->id)
        // The original evidence is preserved.
        ->and($attendance->checked_in_at)->not->toBeNull()
        ->and($attendance->checked_in_by)->toBe($this->user->id);
});

it('leaves the other days untouched when cancelling one', function () {
    $registration = SeminarRegistration::factory()->verified()->create();

    $day1 = Attendance::factory()->day(1)->create([
        'seminar_registration_id' => $registration->id, 'activity_type' => 'seminar', 'checked_in_by' => $this->user->id,
    ]);
    $day3 = Attendance::factory()->day(3)->create([
        'seminar_registration_id' => $registration->id, 'activity_type' => 'seminar', 'checked_in_by' => $this->user->id,
    ]);

    callCancelAction('cancelCheckInDay1', seminarRow($registration));

    expect($day1->refresh()->cancelled_at)->not->toBeNull()
        ->and($day3->refresh()->cancelled_at)->toBeNull();
});

it('offers cancel only for days that are actually checked in', function () {
    $registration = SeminarRegistration::factory()->verified()->create();

    Attendance::factory()->day(1)->create([
        'seminar_registration_id' => $registration->id, 'activity_type' => 'seminar', 'checked_in_by' => $this->user->id,
    ]);

    $row = seminarRow($registration);

    livewire(ListAttendanceRoster::class)
        ->assertTableActionVisible('cancelCheckInDay1', $row)
        ->assertTableActionHidden('cancelCheckInDay2', $row)
        ->assertTableActionHidden('cancelCheckInDay3', $row);
});

it('shows the day as open again after cancelling', function () {
    $registration = SeminarRegistration::factory()->verified()->create();

    Attendance::factory()->day(2)->create([
        'seminar_registration_id' => $registration->id, 'activity_type' => 'seminar', 'checked_in_by' => $this->user->id,
    ]);

    $row = seminarRow($registration);

    // Before: day 2 is in, so its check-in button is hidden.
    livewire(ListAttendanceRoster::class)
        ->assertTableActionHidden('checkInDay2', $row)
        ->assertTableActionVisible('cancelCheckInDay2', $row);

    callCancelAction('cancelCheckInDay2', $row);

    // After: day 2 is open again, so check-in returns and cancel is gone.
    livewire(ListAttendanceRoster::class)
        ->assertTableActionVisible('checkInDay2', $row)
        ->assertTableActionHidden('cancelCheckInDay2', $row);
});

it('does not count a cancelled day as checked in on the roster', function () {
    $registration = SeminarRegistration::factory()->verified()->create();

    Attendance::factory()->day(1)->cancelled()->create([
        'seminar_registration_id' => $registration->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $this->user->id,
    ]);

    $row = seminarRow($registration);

    expect($registration->fresh()->seminarAttendanceForDay(1))->toBeNull()
        ->and($registration->fresh()->nextOpenSeminarDay())->toBe(1);
});

it('hides cancel from a role without the check-in permission', function () {
    $viewer = User::factory()->create();
    $viewer->syncRoles([]);
    $viewer->givePermissionTo('view attendances');
    actingAs($viewer);

    $registration = SeminarRegistration::factory()->verified()->create();
    Attendance::factory()->day(1)->create([
        'seminar_registration_id' => $registration->id, 'activity_type' => 'seminar', 'checked_in_by' => $this->user->id,
    ]);

    livewire(ListAttendanceRoster::class)
        ->assertOk()
        ->assertTableActionHidden('cancelCheckInDay1', seminarRow($registration));
});

it('cancels a hands-on check-in', function () {
    $handsOn = HandsOn::factory()->create();
    $registration = HandsOnRegistration::factory()->verified()->create([
        'seminar_registration_id' => null,
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'hands_on',
    ]);

    $attendance = Attendance::factory()->handsOn()->create([
        'seminar_registration_id' => null,
        'hands_on_registration_id' => $registration->id,
        'checked_in_by' => $this->user->id,
    ]);

    $row = AttendanceRoster::where('participant_key', 'hands_on-'.$registration->id)->sole();

    callCancelAction('cancelCheckInHandsOn', $row);

    expect($attendance->refresh()->cancelled_at)->not->toBeNull()
        ->and($registration->fresh()->activeAttendance)->toBeNull();
});

it('lets a cancelled day be checked in again, reusing the same row', function () {
    $registration = SeminarRegistration::factory()->verified()->create();

    $attendance = Attendance::factory()->day(1)->create([
        'seminar_registration_id' => $registration->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $this->user->id,
    ]);

    $row = seminarRow($registration);
    callCancelAction('cancelCheckInDay1', $row);

    // A different staff member re-checks the day in.
    $other = User::factory()->create();
    $other->assignRole('Super Admin');
    actingAs($other);

    livewire(ListAttendanceRoster::class)->instance()->getTable()->getAction('checkInDay1')->call(['record' => $row]);

    $attendance->refresh();

    expect(Attendance::query()->where('seminar_registration_id', $registration->id)->count())->toBe(1)
        ->and($attendance->cancelled_at)->toBeNull()
        ->and($attendance->cancelled_by)->toBeNull()
        ->and($attendance->checked_in_by)->toBe($other->id);
});

/**
 * Requested: the desk reads sessions by their short code, not the full title.
 */
it('shows the hands-on code rather than the session title', function () {
    $handsOn = HandsOn::factory()->create([
        'name' => 'Principles of Tooth Extraction: Preserving Bone and Soft Tissue',
        'ho_code' => 'HO-02',
    ]);

    HandsOnRegistration::factory()->verified()->create([
        'seminar_registration_id' => null,
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'hands_on',
    ]);

    livewire(ListAttendanceRoster::class)
        ->assertOk()
        ->assertSee('HO-02')
        ->assertDontSee('Principles of Tooth Extraction');
});

it('shows the seminar package column before the hands-on sessions', function () {
    $package = Seminar::create([
        'name' => 'Seminar (+ Lunch)', 'code' => 'PKG-TEST', 'applies_to' => 'all',
        'currency' => 'IDR', 'original_price' => 750000, 'discounted_price' => 750000,
        'max_seats' => 500, 'includes_lunch' => true, 'is_early_bird' => false,
        'is_active' => true, 'sort_order' => 1,
    ]);

    $registration = SeminarRegistration::factory()->verified()->create(['seminar_id' => $package->id]);

    // The cell renders the short package name, not "name (price label)".
    livewire(ListAttendanceRoster::class)
        ->assertOk()
        ->assertSee(__('seminar.package_name_with_lunch'))
        ->assertDontSee('Snack + Lunch');
});

it('leaves the seminar package empty for a standalone hands-on participant', function () {
    $handsOn = HandsOn::factory()->create(['ho_code' => 'HO-99']);
    HandsOnRegistration::factory()->verified()->create([
        'seminar_registration_id' => null,
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'hands_on',
    ]);

    livewire(ListAttendanceRoster::class)
        ->assertOk()
        ->assertSee('HO-99');
});

it('checks out a single day from the roster, leaving other days open', function () {
    $registration = SeminarRegistration::factory()->verified()->create();

    $day1 = Attendance::factory()->day(1)->create([
        'seminar_registration_id' => $registration->id, 'activity_type' => 'seminar', 'checked_in_by' => $this->user->id,
    ]);
    $day2 = Attendance::factory()->day(2)->create([
        'seminar_registration_id' => $registration->id, 'activity_type' => 'seminar', 'checked_in_by' => $this->user->id,
    ]);

    callCancelAction('checkOutDay1', seminarRow($registration));

    expect($day1->refresh()->checked_out_at)->not->toBeNull()
        ->and($day1->checked_out_by)->toBe($this->user->id)
        ->and($day2->refresh()->checked_out_at)->toBeNull();
});

it('offers per-day check-out only for days that are in and still open', function () {
    $registration = SeminarRegistration::factory()->verified()->create();

    Attendance::factory()->day(1)->create([
        'seminar_registration_id' => $registration->id, 'activity_type' => 'seminar', 'checked_in_by' => $this->user->id,
    ]);
    Attendance::factory()->day(2)->checkedOut()->create([
        'seminar_registration_id' => $registration->id, 'activity_type' => 'seminar', 'checked_in_by' => $this->user->id,
    ]);

    $row = seminarRow($registration);

    livewire(ListAttendanceRoster::class)
        ->assertTableActionVisible('checkOutDay1', $row)   // in, still open
        ->assertTableActionHidden('checkOutDay2', $row)   // already out
        ->assertTableActionHidden('checkOutDay3', $row);  // never checked in
});

it('keeps the first day check-out when run twice', function () {
    $registration = SeminarRegistration::factory()->verified()->create();
    $attendance = Attendance::factory()->day(1)->create([
        'seminar_registration_id' => $registration->id, 'activity_type' => 'seminar', 'checked_in_by' => $this->user->id,
    ]);

    $row = seminarRow($registration);
    callCancelAction('checkOutDay1', $row);
    $first = $attendance->refresh()->checked_out_at;

    $other = User::factory()->create();
    $other->assignRole('Super Admin');
    actingAs($other);
    callCancelAction('checkOutDay1', $row);

    expect($attendance->refresh()->checked_out_at->equalTo($first))->toBeTrue();
});
