<?php

use App\Mail\HandsOnRegistrationConfirmation;
use App\Models\Country;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    $country = Country::factory()->create([
        'name' => 'Indonesia',
        'is_indonesia' => true,
    ]);

    $handsOn = HandsOn::factory()->create([
        'name' => 'Test Hands On Session',
        'ho_code' => 'HO-001',
        'doctor_name' => 'Dr. Test',
        'event_date' => '2026-11-14',
        'price' => 500000,
        'original_price' => 500000,
    ]);
});

it('builds envelope with correct subject', function () {
    $registration = HandsOnRegistration::factory()->create();

    $mail = new HandsOnRegistrationConfirmation($registration);

    $envelope = $mail->envelope();
    expect($envelope->subject)->toBe(
        trans('seminar.email_hands_on_registration_confirmation_subject', [
            'code' => $registration->registration_code,
        ])
    );
});

it('has correct view', function () {
    $registration = HandsOnRegistration::factory()->create();
    $mail = new HandsOnRegistrationConfirmation($registration);
    $content = $mail->content();

    expect($content->view)->toBe('emails.hands-on-registration-confirmation');
});

it('renders email with participant details for Indonesian registrants', function () {
    $country = Country::where('is_indonesia', true)->first();
    $registration = HandsOnRegistration::factory()->create([
        'country_id' => $country->id,
        'name_license' => 'dr. Budi Santoso',
        'email' => 'budi@example.com',
        'phone' => '08123456789',
        'nik' => '1234567890123456',
        'pdgi_branch' => 'Jakarta',
        'kompetensi' => 'Umum',
    ]);
    $mail = new HandsOnRegistrationConfirmation($registration);

    $rendered = $mail->render();

    expect($rendered)
        ->toContain($registration->registration_code)
        ->toContain($registration->name_license)
        ->toContain($registration->email)
        ->toContain($registration->phone)
        ->toContain($registration->nik)
        ->toContain($registration->pdgi_branch)
        ->toContain($registration->kompetensi);
});

it('renders email with participant details for international registrants', function () {
    $country = Country::factory()->create([
        'name' => 'Malaysia',
        'code' => 'MY',
        'is_indonesia' => false,
    ]);
    $registration = HandsOnRegistration::factory()->create([
        'country_id' => $country->id,
        'name' => 'John Tan',
        'email' => 'john@example.com',
        'phone' => '60123456789',
        'status' => 'Dentist',
    ]);
    $mail = new HandsOnRegistrationConfirmation($registration);

    $rendered = $mail->render();

    expect($rendered)
        ->toContain($registration->registration_code)
        ->toContain($registration->name)
        ->toContain($registration->email)
        ->toContain($registration->phone)
        ->toContain($registration->status)
        ->toContain($country->name);
});

it('renders email with hands-on session details', function () {
    $handsOn = HandsOn::first();
    $registration = HandsOnRegistration::factory()->create([
        'hands_on_id' => $handsOn->id,
    ]);
    $mail = new HandsOnRegistrationConfirmation($registration);

    $rendered = $mail->render();

    expect($rendered)
        ->toContain($handsOn->name)
        ->toContain($handsOn->doctor_name)
        ->toContain($handsOn->ho_code)
        ->toContain($handsOn->event_date->format('d F Y'));
});

it('renders email with payment information', function () {
    $registration = HandsOnRegistration::factory()->create([
        'payment_method' => 'qris',
        'payment_status' => 'pending',
    ]);
    $mail = new HandsOnRegistrationConfirmation($registration);

    $rendered = $mail->render();

    expect($rendered)
        ->toContain(strtoupper($registration->payment_method))
        ->toContain(ucfirst($registration->payment_status));
});

it('can be sent via mail facade', function () {
    Mail::fake();

    $registration = HandsOnRegistration::factory()->create();

    Mail::to($registration->email)->send(new HandsOnRegistrationConfirmation($registration));

    Mail::assertSent(HandsOnRegistrationConfirmation::class, function ($mail) use ($registration) {
        return $mail->registration->is($registration);
    });
});
