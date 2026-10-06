<?php

use App\Models\DigitalWorkshop;
use App\Models\DigitalWorkshopRegistration;
use App\Models\SeminarRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('generates a registration code with the JADE-DW prefix', function () {
    $code = DigitalWorkshopRegistration::generateRegistrationCode();

    expect($code)->toStartWith('JADE-DW-2026-');
});

test('increments the registration code sequence', function () {
    DigitalWorkshopRegistration::factory()->create(['registration_code' => 'JADE-DW-2026-000001']);
    DigitalWorkshopRegistration::factory()->create(['registration_code' => 'JADE-DW-2026-000002']);

    $next = DigitalWorkshopRegistration::generateRegistrationCode();

    expect($next)->toBe('JADE-DW-2026-000003');
});

test('reports bundled only when seminar_registration_id is set', function () {
    $standalone = DigitalWorkshopRegistration::factory()->create(['seminar_registration_id' => null]);
    $bundled = DigitalWorkshopRegistration::factory()->create([
        'seminar_registration_id' => SeminarRegistration::factory()->create()->id,
    ]);

    expect($standalone->isBundled())->toBeFalse();
    expect($bundled->isBundled())->toBeTrue();
});

test('resolves the recipient email from its own email column', function () {
    $registration = DigitalWorkshopRegistration::factory()->create(['email' => 'attendee@example.com']);

    expect($registration->recipientEmail())->toBe('attendee@example.com');
});

test('falls back to the seminar registration email when own email is empty', function () {
    $seminar = SeminarRegistration::factory()->create(['email' => 'from-seminar@example.com']);
    $registration = DigitalWorkshopRegistration::factory()->create([
        'email' => '',
        'seminar_registration_id' => $seminar->id,
    ]);

    expect($registration->recipientEmail())->toBe('from-seminar@example.com');
});

test('falls back to en when language is absent', function () {
    $registration = DigitalWorkshopRegistration::factory()->create(['language' => '']);

    expect($registration->recipientLanguage())->toBe('en');
});

test('casts amount to integer', function () {
    $registration = DigitalWorkshopRegistration::factory()->create(['amount' => 999000]);

    expect($registration->refresh()->amount)->toBeInt();
    expect($registration->amount)->toBe(999000);
});

test('resolves its workshop relation', function () {
    $workshop = DigitalWorkshop::factory()->create();
    $registration = DigitalWorkshopRegistration::factory()->create(['digital_workshop_id' => $workshop->id]);

    expect($registration->digitalWorkshop->id)->toBe($workshop->id);
});

test('reports pending, verified and rejected states', function () {
    $pending = DigitalWorkshopRegistration::factory()->create(['payment_status' => 'pending']);
    $verified = DigitalWorkshopRegistration::factory()->create(['payment_status' => 'verified']);
    $rejected = DigitalWorkshopRegistration::factory()->create(['payment_status' => 'rejected']);

    expect($pending->isPending())->toBeTrue();
    expect($verified->isVerified())->toBeTrue();
    expect($rejected->isRejected())->toBeTrue();
    expect($pending->isVerified())->toBeFalse();
});
