<?php

use App\Filament\Resources\Attendances\Pages\ListAttendanceRoster;
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

function rosterRowForRegistration(SeminarRegistration $registration): AttendanceRoster
{
    return AttendanceRoster::where('participant_key', 'seminar-'.$registration->id)->sole();
}

function verifyPageUrlFor(AttendanceRoster $row): ?string
{
    return livewire(ListAttendanceRoster::class)
        ->instance()
        ->getTable()
        ->getAction('showVerifyPage')
        ->record($row)
        ->getUrl();
}

it('links a seminar row to its attendance verify page', function () {
    $registration = SeminarRegistration::factory()->verified()->create();
    $row = rosterRowForRegistration($registration);

    expect(verifyPageUrlFor($row))->toBe(url('/attendance/verify/'.$registration->qr_token));

    livewire(ListAttendanceRoster::class)
        ->assertOk()
        ->assertTableActionVisible('showVerifyPage', $row);
});

it('links a standalone hands-on row to its attendance verify page', function () {
    $handsOn = HandsOn::factory()->create();
    $registration = HandsOnRegistration::factory()->verified()->create([
        'seminar_registration_id' => null,
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'hands_on',
        'qr_token' => str_repeat('a', 64),
    ]);

    $row = AttendanceRoster::where('participant_key', 'hands_on-'.$registration->id)->sole();

    expect(verifyPageUrlFor($row))->toBe(url('/attendance/verify/'.$registration->qr_token));

    livewire(ListAttendanceRoster::class)
        ->assertOk()
        ->assertTableActionVisible('showVerifyPage', $row);
});

it('hides the verify page action when the registration has no qr token', function () {
    $handsOn = HandsOn::factory()->create();
    $registration = HandsOnRegistration::factory()->verified()->create([
        'seminar_registration_id' => null,
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'hands_on',
        'qr_token' => null,
    ]);

    $row = AttendanceRoster::where('participant_key', 'hands_on-'.$registration->id)->sole();

    expect(verifyPageUrlFor($row))->toBeNull();

    livewire(ListAttendanceRoster::class)
        ->assertOk()
        ->assertTableActionHidden('showVerifyPage', $row);
});
