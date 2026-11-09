<?php

use App\Filament\Resources\Attendances\Pages\ListHandsOnAttendances;
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
 * The hands-on roster is keyed on registrations, so search has to work on the
 * session name and the participant name that live on related tables.
 */
it('searches the hands-on roster by session name', function () {
    $implant = HandsOn::create([
        'name' => 'Dental Implant Workshop', 'currency' => 'IDR',
        'original_price' => 500000, 'is_active' => true, 'event_date' => '2026-11-13',
    ]);
    $composite = HandsOn::create([
        'name' => 'Aesthetic Composite Restoration', 'currency' => 'IDR',
        'original_price' => 450000, 'is_active' => true, 'event_date' => '2026-11-15',
    ]);

    $implantRegistration = HandsOnRegistration::create([
        'registration_code' => HandsOnRegistration::generateRegistrationCode(),
        'hands_on_id' => $implant->id,
        'registration_type' => 'hands_on',
        'payment_status' => 'verified',
        'verified_at' => now(),
    ]);

    $compositeRegistration = HandsOnRegistration::create([
        'registration_code' => HandsOnRegistration::generateRegistrationCode(),
        'hands_on_id' => $composite->id,
        'registration_type' => 'hands_on',
        'payment_status' => 'verified',
        'verified_at' => now(),
    ]);

    livewire(ListHandsOnAttendances::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$implantRegistration, $compositeRegistration])
        ->searchTable('Dental Implant')
        ->assertCanSeeTableRecords([$implantRegistration])
        ->assertCanNotSeeTableRecords([$compositeRegistration]);
});

it('searches the hands-on roster by participant name', function () {
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

    livewire(ListHandsOnAttendances::class)
        ->searchTable('Ayu')
        ->assertCanSeeTableRecords([$ayu])
        ->assertCanNotSeeTableRecords([$budi]);
});

it('searches the hands-on roster without matching anything', function () {
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

    livewire(ListHandsOnAttendances::class)
        ->searchTable('Nothing Matches This')
        ->assertCanNotSeeTableRecords([$registration]);
});
