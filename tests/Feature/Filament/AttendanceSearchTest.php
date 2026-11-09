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

/**
 * The roster is one row per participant, so search has to work across the
 * view's own columns rather than a single registration table.
 */
it('searches the roster by participant name', function () {
    $handsOn = HandsOn::create([
        'name' => 'Dental Implant Workshop', 'currency' => 'IDR',
        'original_price' => 500000, 'is_active' => true, 'event_date' => '2026-11-13',
    ]);

    $ayu = HandsOnRegistration::create([
        'registration_code' => HandsOnRegistration::generateRegistrationCode(),
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'hands_on',
        'payment_status' => 'verified',
        'verified_at' => now(),
        'name_license' => 'Ayu Kartika',
    ]);

    $budi = HandsOnRegistration::create([
        'registration_code' => HandsOnRegistration::generateRegistrationCode(),
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'hands_on',
        'payment_status' => 'verified',
        'verified_at' => now(),
        'name_license' => 'Budi Santoso',
    ]);

    livewire(ListAttendanceRoster::class)
        ->assertOk()
        ->searchTable('Ayu')
        ->assertCanSeeTableRecords([AttendanceRoster::find('hands_on-'.$ayu->id)])
        ->assertCanNotSeeTableRecords([AttendanceRoster::find('hands_on-'.$budi->id)]);
});

it('searches the roster by registration code', function () {
    $target = SeminarRegistration::factory()->verified()->create();
    $other = SeminarRegistration::factory()->verified()->create();

    livewire(ListAttendanceRoster::class)
        ->searchTable($target->registration_code)
        ->assertCanSeeTableRecords([AttendanceRoster::find('seminar-'.$target->id)])
        ->assertCanNotSeeTableRecords([AttendanceRoster::find('seminar-'.$other->id)]);
});

it('searches the roster without matching anything', function () {
    $registration = SeminarRegistration::factory()->verified()->create();

    livewire(ListAttendanceRoster::class)
        ->searchTable('Nothing Matches This')
        ->assertCanNotSeeTableRecords([AttendanceRoster::find('seminar-'.$registration->id)]);
});
