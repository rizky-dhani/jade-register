<?php

use App\Filament\Resources\HandsOnRegistrations\HandsOnRegistrationResource;
use App\Filament\Resources\HandsOnRegistrations\Pages\ListHandsOnRegistrations;
use App\Models\Country;
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

    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    actingAs($user);

    Country::factory()->indonesia()->create(['id' => 1]);
    Seminar::create([
        'code' => 'test-seminar', 'name' => 'Test Seminar', 'currency' => 'IDR',
        'original_price' => 500000, 'applies_to' => 'local', 'is_active' => true,
    ]);
    $this->handsOn = HandsOn::create([
        'name' => 'HO Test', 'currency' => 'IDR', 'original_price' => 100000,
        'is_active' => true, 'event_date' => '2026-11-13',
    ]);
});

it('renders the view page', function () {
    $registration = HandsOnRegistration::create([
        'registration_code' => HandsOnRegistration::generateRegistrationCode(),
        'hands_on_id' => $this->handsOn->id,
        'registration_type' => 'standalone',
        'payment_status' => 'pending',
        'name_license' => 'Dr. View',
        'email' => 'view@example.com',
        'phone' => '0812',
        'status' => 'registered',
        'country_id' => 1,
        'payment_method' => 'bank_transfer',
        'language' => 'id',
    ]);

    livewire(ListHandsOnRegistrations::class)
        ->assertSuccessful();

    $response = $this->get(HandsOnRegistrationResource::getUrl('view', ['record' => $registration]));
    $response->assertSuccessful()
        ->assertSee('Dr. View')
        ->assertSee('HO Test');
});

it('filters by registration type', function () {
    $seminar = SeminarRegistration::create([
        'registration_code' => SeminarRegistration::generateRegistrationCode(),
        'email' => 'bundled@example.com',
        'name_license' => 'Dr. Bundled',
        'phone' => '0813',
        'country_id' => 1,
        'language' => 'id',
        'registration_type' => 'regular',
        'seminar_id' => 1,
        'payment_method' => 'bank_transfer',
        'amount' => 500000,
        'currency' => 'IDR',
        'payment_status' => 'pending',
        'status' => 'registered',
    ]);

    HandsOnRegistration::create([
        'registration_code' => HandsOnRegistration::generateRegistrationCode(),
        'hands_on_id' => $this->handsOn->id,
        'registration_type' => 'standalone',
        'payment_status' => 'pending',
        'name_license' => 'Dr. Standalone',
        'email' => 'sa@example.com',
        'status' => 'registered',
        'country_id' => 1,
        'payment_method' => 'bank_transfer',
        'language' => 'id',
    ]);

    HandsOnRegistration::create([
        'registration_code' => HandsOnRegistration::generateRegistrationCode(),
        'hands_on_id' => $this->handsOn->id,
        'seminar_registration_id' => $seminar->id,
        'registration_type' => 'bundled',
        'payment_status' => 'pending',
        'email' => 'sa2@example.com',
        'status' => 'registered',
        'country_id' => 1,
        'payment_method' => 'bank_transfer',
        'language' => 'id',
    ]);

    livewire(ListHandsOnRegistrations::class)
        ->filterTable('registration_type', 'bundled')
        ->assertCanSeeTableRecords(HandsOnRegistration::where('registration_type', 'bundled')->get())
        ->assertCanNotSeeTableRecords(HandsOnRegistration::where('registration_type', 'standalone')->get());
});
