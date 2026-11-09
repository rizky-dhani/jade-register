<?php

use App\Filament\Resources\Attendances\Pages\ListAttendanceRoster;
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

function packageWithLunch(bool $includesLunch): Seminar
{
    return Seminar::create([
        'name' => $includesLunch ? 'Seminar (+ Lunch)' : 'Seminar',
        'code' => 'roster-'.($includesLunch ? 'lunch' : 'snack').'-'.uniqid(),
        'applies_to' => 'all',
        'currency' => 'IDR',
        'original_price' => 900000,
        'includes_lunch' => $includesLunch,
    ]);
}

function participantForPackage(Seminar $seminar): AttendanceRoster
{
    $registration = SeminarRegistration::factory()->verified()->create([
        'name_license' => $seminar->includes_lunch ? 'Ayu Lunch' : 'Budi Snack',
        'seminar_id' => $seminar->id,
    ]);

    return AttendanceRoster::where('participant_key', 'seminar-'.$registration->id)->sole();
}

it('filters the roster to seminar participants with the lunch package', function () {
    $lunchRow = participantForPackage(packageWithLunch(true));
    $snackRow = participantForPackage(packageWithLunch(false));

    livewire(ListAttendanceRoster::class)
        ->filterTable('seminar_package', true)
        ->assertCanSeeTableRecords([$lunchRow])
        ->assertCanNotSeeTableRecords([$snackRow]);
});

it('filters the roster to seminar participants without lunch', function () {
    $lunchRow = participantForPackage(packageWithLunch(true));
    $snackRow = participantForPackage(packageWithLunch(false));

    livewire(ListAttendanceRoster::class)
        ->filterTable('seminar_package', false)
        ->assertCanSeeTableRecords([$snackRow])
        ->assertCanNotSeeTableRecords([$lunchRow]);
});

it('hides standalone hands-on rows from both package filters', function () {
    $lunchRow = participantForPackage(packageWithLunch(true));
    $snackRow = participantForPackage(packageWithLunch(false));

    $handsOn = HandsOn::factory()->create();
    $standalone = HandsOnRegistration::factory()->verified()->create([
        'seminar_registration_id' => null,
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'hands_on',
    ]);
    $handsOnRow = AttendanceRoster::where('participant_key', 'hands_on-'.$standalone->id)->sole();

    livewire(ListAttendanceRoster::class)
        ->filterTable('seminar_package', true)
        ->assertCanSeeTableRecords([$lunchRow])
        ->assertCanNotSeeTableRecords([$handsOnRow])
        ->filterTable('seminar_package', false)
        ->assertCanSeeTableRecords([$snackRow])
        ->assertCanNotSeeTableRecords([$handsOnRow]);
});

it('shows every participant when the package filter is cleared', function () {
    $lunchRow = participantForPackage(packageWithLunch(true));
    $snackRow = participantForPackage(packageWithLunch(false));

    livewire(ListAttendanceRoster::class)
        ->assertCanSeeTableRecords([$lunchRow, $snackRow]);
});
