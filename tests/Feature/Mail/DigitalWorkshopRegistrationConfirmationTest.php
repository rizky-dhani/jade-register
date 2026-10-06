<?php

use App\Mail\DigitalWorkshopRegistrationConfirmation;
use App\Models\Country;
use App\Models\DigitalWorkshop;
use App\Models\DigitalWorkshopRegistration;
use App\Models\SeminarRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Country::create([
        'id' => 1,
        'name' => 'Indonesia',
        'code' => 'ID',
        'is_indonesia' => true,
        'phone_code' => '62',
    ]);

    $this->workshop = DigitalWorkshop::factory()->create([
        'name' => 'Digital Workshop',
        'event_date' => '2026-11-21',
        'event_time' => '09:00:00',
        'location' => 'Jakarta',
    ]);
});

test('builds the envelope subject with the registration code', function () {
    $registration = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'registration_code' => 'JADE-DW-2026-000007',
    ]);

    $mailable = new DigitalWorkshopRegistrationConfirmation($registration);

    expect($mailable->envelope()->subject)->toContain('JADE-DW-2026-000007');
});

test('uses the correct view', function () {
    $registration = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
    ]);

    $mailable = new DigitalWorkshopRegistrationConfirmation($registration);

    expect($mailable->content()->view)->toBe('emails.digital-workshop-registration-confirmation');
});

test('renders local attendee details', function () {
    $registration = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'name_license' => 'Dr. Local Attendee',
        'nik' => '1234567890123456',
        'pdgi_branch' => 'Jakarta',
        'kompetensi' => 'Dokter Gigi Umum',
    ]);

    $mailable = new DigitalWorkshopRegistrationConfirmation($registration);

    $mailable->assertSeeInHtml('Dr. Local Attendee', false);
    $mailable->assertSeeInHtml('1234567890123456', false);
    $mailable->assertSeeInHtml('Jakarta', false);
    $mailable->assertSeeInHtml('Dokter Gigi Umum', false);
});

test('renders international attendee details', function () {
    $registration = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'name' => 'International Attendee',
        'name_license' => null,
        'nik' => null,
        'pdgi_branch' => null,
        'kompetensi' => null,
        'status' => 'Dentist',
        'country_id' => Country::create([
            'name' => 'Malaysia',
            'code' => 'MY',
            'is_indonesia' => false,
            'phone_code' => '60',
        ])->id,
    ]);

    $mailable = new DigitalWorkshopRegistrationConfirmation($registration);

    $mailable->assertSeeInHtml('International Attendee', false);
    $mailable->assertSeeInHtml('Dentist', false);
});

test('renders the amount and payment status', function () {
    $registration = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'amount' => 999000,
        'payment_status' => 'pending',
    ]);

    $mailable = new DigitalWorkshopRegistrationConfirmation($registration);

    $mailable->assertSeeInHtml('999.000', false);
});

test('renders the bundle note only for bundled registrations', function () {
    $standalone = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'registration_type' => 'standalone',
        'seminar_registration_id' => null,
    ]);

    $bundled = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'registration_type' => 'bundled',
        'seminar_registration_id' => SeminarRegistration::factory()->create()->id,
    ]);

    // The mailable itself does not switch locale — Mail::locale() does that at
    // send time. Under the default app locale the note renders in English.
    $note = trans('seminar.digital_workshop_bundle_applied');

    (new DigitalWorkshopRegistrationConfirmation($standalone))->assertDontSeeInHtml($note, false);
    (new DigitalWorkshopRegistrationConfirmation($bundled))->assertSeeInHtml($note, false);
});
