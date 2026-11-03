<?php

use App\Filament\Resources\Attendances\Pages\ListHandsOnAttendances;
use App\Filament\Resources\Attendances\Pages\ListSeminarAttendances;
use App\Models\Attendance;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Filament\Actions\Testing\TestAction;
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
function callManualCheckInAction(string $page, array $data): void
{
    livewire($page)->instance()->getTable()->getAction('manualCheckIn')->call(['data' => $data]);
}

it('checks in a verified seminar participant from the seminar attendance list', function () {
    $registration = SeminarRegistration::factory()->verified()->create();

    callManualCheckInAction(ListSeminarAttendances::class, [
        'seminar_registration_id' => $registration->id,
    ]);

    $attendance = Attendance::query()
        ->where('seminar_registration_id', $registration->id)
        ->where('activity_type', 'seminar')
        ->sole();

    expect($attendance->checked_in_at)->not->toBeNull()
        ->and($attendance->checked_in_by)->toBe($this->user->id)
        ->and($attendance->hands_on_registration_id)->toBeNull();
});

it('checks in a verified hands-on participant from the hands-on attendance list', function () {
    $seminar = SeminarRegistration::factory()->verified()->create();
    $handsOn = HandsOn::factory()->create();
    $registration = HandsOnRegistration::factory()->verified()->create([
        'seminar_registration_id' => $seminar->id,
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'hands_on',
    ]);

    callManualCheckInAction(ListHandsOnAttendances::class, [
        'hands_on_registration_id' => $registration->id,
    ]);

    $attendance = Attendance::query()
        ->where('hands_on_registration_id', $registration->id)
        ->where('activity_type', 'hands_on')
        ->sole();

    expect($attendance->checked_in_at)->not->toBeNull()
        ->and($attendance->checked_in_by)->toBe($this->user->id)
        ->and($attendance->seminar_registration_id)->toBe($seminar->id);
});

it('leaves an existing check-in untouched when the participant is checked in again', function () {
    $registration = SeminarRegistration::factory()->verified()->create();
    $originalStaff = User::factory()->create();
    $originalStaff->assignRole('Super Admin');

    callManualCheckInAction(ListSeminarAttendances::class, [
        'seminar_registration_id' => $registration->id,
    ]);

    $attendance = Attendance::query()
        ->where('seminar_registration_id', $registration->id)
        ->sole();
    $attendance->update([
        'checked_in_at' => now()->subHour(),
        'checked_in_by' => $originalStaff->id,
    ]);
    $originalCheckedInAt = $attendance->refresh()->checked_in_at;

    callManualCheckInAction(ListSeminarAttendances::class, [
        'seminar_registration_id' => $registration->id,
    ]);

    expect(Attendance::query()->where('seminar_registration_id', $registration->id)->count())->toBe(1)
        ->and($attendance->refresh()->checked_in_by)->toBe($originalStaff->id)
        ->and($attendance->checked_in_at->equalTo($originalCheckedInAt))->toBeTrue();
});

it('refuses a registration whose payment is not verified', function () {
    $pending = SeminarRegistration::factory()->create(['payment_status' => 'pending']);

    callManualCheckInAction(ListSeminarAttendances::class, [
        'seminar_registration_id' => $pending->id,
    ]);

    expect(Attendance::query()->where('seminar_registration_id', $pending->id)->exists())->toBeFalse();
});

it('shows the manual check-in action to desk admins and hides it from participants', function () {
    $desk = User::factory()->create();
    $desk->assignRole('Admin');
    actingAs($desk);

    livewire(ListSeminarAttendances::class)
        ->assertActionVisible(TestAction::make('manualCheckIn')->table());

    $participant = User::factory()->create();
    $participant->assignRole('Participant');
    actingAs($participant);

    livewire(ListSeminarAttendances::class)
        ->assertActionHidden(TestAction::make('manualCheckIn')->table());
});
