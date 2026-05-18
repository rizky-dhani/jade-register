<?php

use App\Enums\HandsOnStatus;
use App\Models\Country;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

beforeEach(function () {
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
        'name' => 'Test Hands On',
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
});

it('creates a standalone HandsOnRegistration without seminar_registration_id', function () {
    $handsOn = HandsOn::first();
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
        'payment_status' => 'verified',
        'verified_at' => now(),
    ]);

    assertDatabaseCount('hands_on_registrations', 1);
    assertDatabaseCount('seminar_registrations', 0);

    assertDatabaseHas('hands_on_registrations', [
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'hands_on',
        'name_license' => 'Dr. Test User',
        'email' => 'test@example.com',
    ]);

    expect($registration->seminar_registration_id)->toBeNull();
})->group('hands-on', 'registration');

it('creates a combined HandsOnRegistration linked to an existing seminar registration', function () {
    $handsOn = HandsOn::first();
    $country = Country::first();

    $seminarRegistration = SeminarRegistration::create([
        'country_id' => $country->id,
        'name_license' => 'Dr. Seminar User',
        'name' => 'Dr. Seminar User',
        'email' => 'seminar@example.com',
        'phone' => '081234567890',
        'nik' => '1234567890123456',
        'pdgi_branch' => 'Jakarta',
        'kompetensi' => 'Dokter Gigi Umum',
        'registration_code' => SeminarRegistration::generateRegistrationCode(),
        'language' => 'id',
        'registration_type' => 'seminar_only',
        'wants_hands_on' => true,
        'wants_poster_competition' => false,
        'amount' => 0,
        'currency' => 'IDR',
    ]);

    HandsOnRegistration::create([
        'seminar_registration_id' => $seminarRegistration->id,
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'combined',
        'payment_status' => 'verified',
        'verified_at' => now(),
    ]);

    assertDatabaseCount('seminar_registrations', 1);
    assertDatabaseCount('hands_on_registrations', 1);

    assertDatabaseHas('hands_on_registrations', [
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'combined',
        'seminar_registration_id' => $seminarRegistration->id,
    ]);
})->group('hands-on', 'registration');
