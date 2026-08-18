<?php

use App\Enums\HandsOnStatus;
use App\Filament\Resources\HandsOnRegistrations\Pages\EditHandsOnRegistration;
use App\Models\Country;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);

    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');
    actingAs($this->user);

    Country::create([
        'id' => 1,
        'name' => 'Indonesia',
        'code' => 'ID',
        'is_indonesia' => true,
        'phone_code' => '62',
    ]);

    Setting::create([
        'key' => 'max_participants',
        'value' => 100,
        'type' => 'integer',
    ]);

    HandsOn::create([
        'name' => 'Test Hands On Day 1',
        'ho_code' => 'HO-001',
        'doctor_name' => 'Dr. Test',
        'description' => 'Test description',
        'event_date' => '2026-11-13',
        'max_seats' => 10,
        'price' => 500000,
        'original_price' => 500000,
        'currency' => 'IDR',
        'is_active' => true,
        'status' => HandsOnStatus::PUBLISHED,
    ]);

    HandsOn::create([
        'name' => 'Test Hands On Day 2',
        'ho_code' => 'HO-002',
        'doctor_name' => 'Dr. Test',
        'description' => 'Test description',
        'event_date' => '2026-11-14',
        'max_seats' => 10,
        'price' => 500000,
        'original_price' => 500000,
        'currency' => 'IDR',
        'is_active' => true,
        'status' => HandsOnStatus::PUBLISHED,
    ]);
});

it('pre-fills the selected hands-on radio from the record on edit', function () {
    $handsOn = HandsOn::where('ho_code', 'HO-001')->first();
    $country = Country::first();

    $registration = HandsOnRegistration::create([
        'hands_on_id' => $handsOn->id,
        'country_id' => $country->id,
        'registration_type' => 'hands_on',
        'name_license' => 'Dr. Test User',
        'email' => 'test@example.com',
        'phone' => '081234567890',
        'nik' => '1234567890123456',
        'pdgi_branch' => 'Jakarta',
        'kompetensi' => 'Dokter Gigi Umum',
        'payment_method' => 'bank_transfer',
        'payment_status' => 'pending',
    ]);

    livewire(EditHandsOnRegistration::class, ['record' => $registration->getRouteKey()])
        ->assertOk()
        ->assertSchemaStateSet([
            'selectedHandsOn.2026-11-13' => $handsOn->id,
        ]);
})->group('hands-on', 'registration');

it('updates hands_on_id when the hands-on session is changed on edit', function () {
    $handsOnDay1 = HandsOn::where('ho_code', 'HO-001')->first();
    $handsOnDay2 = HandsOn::where('ho_code', 'HO-002')->first();
    $country = Country::first();

    $registration = HandsOnRegistration::create([
        'hands_on_id' => $handsOnDay1->id,
        'country_id' => $country->id,
        'registration_type' => 'hands_on',
        'name_license' => 'Dr. Test User',
        'email' => 'test@example.com',
        'phone' => '081234567890',
        'nik' => '1234567890123456',
        'pdgi_branch' => 'Jakarta',
        'kompetensi' => 'Dokter Gigi Umum',
        'payment_method' => 'bank_transfer',
        'payment_status' => 'pending',
    ]);

    livewire(EditHandsOnRegistration::class, ['record' => $registration->getRouteKey()])
        ->fillForm([
            'selectedHandsOn.2026-11-14' => $handsOnDay2->id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas('hands_on_registrations', [
        'id' => $registration->id,
        'hands_on_id' => $handsOnDay2->id,
    ]);
})->group('hands-on', 'registration');
