<?php

use App\Models\DigitalWorkshop;
use App\Models\DigitalWorkshopRegistration;
use App\Models\SeminarRegistration;
use App\Services\DigitalWorkshopPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->pricing = app(DigitalWorkshopPricingService::class);
});

test('returns the standalone price when no seminar registration exists', function () {
    $workshop = DigitalWorkshop::factory()->create(['price' => 1199000]);

    $result = ($this->pricing)->forEmail('nobody@example.com', $workshop);

    expect($result['amount'])->toBe(1199000);
    expect($result['registration_type'])->toBe('standalone');
    expect($result['seminar_registration_id'])->toBeNull();
});

test('returns the bundle price for a verified seminar registration', function () {
    $workshop = DigitalWorkshop::factory()->create(['bundle_price' => 999000]);
    $seminar = SeminarRegistration::factory()->create([
        'email' => 'bundle@example.com',
        'payment_status' => 'verified',
    ]);

    $result = ($this->pricing)->forEmail('bundle@example.com', $workshop);

    expect($result['amount'])->toBe(999000);
    expect($result['registration_type'])->toBe('bundled');
    expect($result['seminar_registration_id'])->toBe($seminar->id);
});

test('returns the standalone price for a pending seminar registration', function () {
    $workshop = DigitalWorkshop::factory()->create(['bundle_price' => 999000]);
    SeminarRegistration::factory()->create([
        'email' => 'pending@example.com',
        'payment_status' => 'pending',
    ]);

    $result = ($this->pricing)->forEmail('pending@example.com', $workshop);

    expect($result['amount'])->toBe(1199000);
    expect($result['registration_type'])->toBe('standalone');
});

test('returns the standalone price for a rejected seminar registration', function () {
    $workshop = DigitalWorkshop::factory()->create(['bundle_price' => 999000]);
    SeminarRegistration::factory()->create([
        'email' => 'rejected@example.com',
        'payment_status' => 'rejected',
    ]);

    $result = ($this->pricing)->forEmail('rejected@example.com', $workshop);

    expect($result['amount'])->toBe(1199000);
    expect($result['registration_type'])->toBe('standalone');
});

test('matches email case-insensitively', function () {
    $workshop = DigitalWorkshop::factory()->create(['bundle_price' => 999000]);
    SeminarRegistration::factory()->create([
        'email' => 'budi@example.com',
        'payment_status' => 'verified',
    ]);

    $result = ($this->pricing)->forEmail('Budi@Example.com', $workshop);

    expect($result['amount'])->toBe(999000);
    expect($result['registration_type'])->toBe('bundled');
});

test('reads bundle price from the workshop row, not a constant', function () {
    $workshop = DigitalWorkshop::factory()->create(['bundle_price' => 950000]);
    SeminarRegistration::factory()->create([
        'email' => 'custom@example.com',
        'payment_status' => 'verified',
    ]);

    $result = ($this->pricing)->forEmail('custom@example.com', $workshop);

    expect($result['amount'])->toBe(950000);
});

test('falls back to the standalone price when bundle_price is null', function () {
    $workshop = DigitalWorkshop::factory()->create(['price' => 1199000, 'bundle_price' => null]);
    SeminarRegistration::factory()->create([
        'email' => 'cleared@example.com',
        'payment_status' => 'verified',
    ]);

    $result = ($this->pricing)->forEmail('cleared@example.com', $workshop);

    // (int) null is 0 — a cleared bundle price must not sell the bundle for free.
    expect($result['amount'])->toBe(1199000);
    expect($result['registration_type'])->toBe('standalone');
    expect($result['seminar_registration_id'])->toBeNull();
});

test('never creates a seminar registration', function () {
    $workshop = DigitalWorkshop::factory()->create();
    $before = SeminarRegistration::count();

    ($this->pricing)->forEmail('ghost@example.com', $workshop);

    expect(SeminarRegistration::count())->toBe($before);
});

test('finds the registration when the stored email differs only by case', function () {
    $workshop = DigitalWorkshop::factory()->create(['bundle_price' => 999000]);
    // seminar_registrations.email carries a unique constraint, so one row per
    // email is an enforced invariant. Store mixed case; the lookup must still hit.
    SeminarRegistration::factory()->create([
        'email' => 'Mixed.Case@Example.com',
        'payment_status' => 'verified',
    ]);

    $result = ($this->pricing)->forEmail('mixed.case@example.com', $workshop);

    expect($result['amount'])->toBe(999000);
    expect($result['registration_type'])->toBe('bundled');
});

test('ignores a verified registration belonging to a different email', function () {
    $workshop = DigitalWorkshop::factory()->create(['bundle_price' => 999000]);
    SeminarRegistration::factory()->create([
        'email' => 'someone-else@example.com',
        'payment_status' => 'verified',
    ]);

    $result = ($this->pricing)->forEmail('me@example.com', $workshop);

    expect($result['amount'])->toBe(1199000);
    expect($result['registration_type'])->toBe('standalone');
});

test('does not mutate an existing digital workshop registration', function () {
    $workshop = DigitalWorkshop::factory()->create();
    $registration = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $workshop->id,
        'amount' => 1199000,
    ]);

    ($this->pricing)->forEmail($registration->email, $workshop);

    expect($registration->refresh()->amount)->toBe(1199000);
});
