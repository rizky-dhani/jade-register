<?php

use App\Filament\Resources\Attendances\Pages\ListHandsOnAttendances;
use App\Models\Attendance;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
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
 * hands_on_name is computed from the relationship, so searching it must go
 * through whereHas rather than the default column search.
 */
it('searches the hands-on attendance list by session name', function () {
    $implant = HandsOn::create([
        'name' => 'Dental Implant Workshop', 'currency' => 'IDR',
        'original_price' => 500000, 'is_active' => true, 'event_date' => '2026-11-13',
    ]);
    $composite = HandsOn::create([
        'name' => 'Aesthetic Composite Restoration', 'currency' => 'IDR',
        'original_price' => 450000, 'is_active' => true, 'event_date' => '2026-11-15',
    ]);

    foreach ([$implant, $composite] as $handsOn) {
        $registration = HandsOnRegistration::create([
            'registration_code' => HandsOnRegistration::generateRegistrationCode(),
            'hands_on_id' => $handsOn->id,
            'registration_type' => 'hands_on',
            'payment_status' => 'verified',
            'verified_at' => now(),
        ]);

        Attendance::create([
            'seminar_registration_id' => null,
            'hands_on_registration_id' => $registration->id,
            'activity_type' => 'hands_on',
            'checked_in_at' => now(),
            'checked_in_by' => $this->user->id,
        ]);
    }

    livewire(ListHandsOnAttendances::class)
        ->assertOk()
        ->assertCanSeeTableRecords(Attendance::all())
        ->searchTable('Dental Implant')
        ->assertCanSeeTableRecords(Attendance::whereHas(
            'handsOnRegistration.handsOn',
            fn ($query) => $query->where('name', 'Dental Implant Workshop')
        )->get())
        ->assertCanNotSeeTableRecords(Attendance::whereHas(
            'handsOnRegistration.handsOn',
            fn ($query) => $query->where('name', 'Aesthetic Composite Restoration')
        )->get());
});

it('searches the hands-on attendance list without matching anything', function () {
    $handsOn = HandsOn::create([
        'name' => 'Dental Implant Workshop', 'currency' => 'IDR',
        'original_price' => 500000, 'is_active' => true, 'event_date' => '2026-11-13',
    ]);

    $registration = HandsOnRegistration::create([
        'registration_code' => HandsOnRegistration::generateRegistrationCode(),
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'hands_on',
        'payment_status' => 'verified',
        'verified_at' => now(),
    ]);

    Attendance::create([
        'seminar_registration_id' => null,
        'hands_on_registration_id' => $registration->id,
        'activity_type' => 'hands_on',
        'checked_in_at' => now(),
        'checked_in_by' => $this->user->id,
    ]);

    livewire(ListHandsOnAttendances::class)
        ->searchTable('Nothing Matches This')
        ->assertCanNotSeeTableRecords(Attendance::all());
});
