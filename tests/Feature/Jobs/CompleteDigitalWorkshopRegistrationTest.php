<?php

use App\Jobs\CompleteDigitalWorkshopRegistration;
use App\Mail\DigitalWorkshopRegistrationConfirmation;
use App\Models\DigitalWorkshop;
use App\Models\DigitalWorkshopRegistration;
use App\Services\QrTokenService;
use App\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();

    $this->workshop = DigitalWorkshop::factory()->create([
        'event_date' => '2026-11-21',
        'status' => 'published',
    ]);
});

test('generates a qr token and sends the confirmation email', function () {
    $registration = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'qr_token' => null,
        'confirmation_email_sent_at' => null,
    ]);

    (new CompleteDigitalWorkshopRegistration($registration))
        ->handle(app(QrTokenService::class), app(RegistrationService::class));

    $registration->refresh();

    expect($registration->qr_token)->not->toBeNull();
    expect($registration->confirmation_email_sent_at)->not->toBeNull();
    Mail::assertSent(DigitalWorkshopRegistrationConfirmation::class);
});

test('does not regenerate a token or resend when both already exist', function () {
    $registration = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'qr_token' => 'existing-token-value',
        'confirmation_email_sent_at' => now(),
    ]);

    (new CompleteDigitalWorkshopRegistration($registration))
        ->handle(app(QrTokenService::class), app(RegistrationService::class));

    $registration->refresh();

    expect($registration->qr_token)->toBe('existing-token-value');
    Mail::assertNothingSent();
});

test('sets qr_expires_at from the workshop event date plus one day', function () {
    $registration = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'qr_token' => null,
    ]);

    (new CompleteDigitalWorkshopRegistration($registration))
        ->handle(app(QrTokenService::class), app(RegistrationService::class));

    expect($registration->refresh()->qr_expires_at->toDateString())
        ->toBe('2026-11-22');
});

test('the generated qr token resolves back to this registration', function () {
    $registration = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'qr_token' => null,
    ]);

    (new CompleteDigitalWorkshopRegistration($registration))
        ->handle(app(QrTokenService::class), app(RegistrationService::class));

    $resolved = app(QrTokenService::class)->validate($registration->refresh()->qr_token);

    expect($resolved)->toBeInstanceOf(DigitalWorkshopRegistration::class);
    expect($resolved->id)->toBe($registration->id);
});

test('returns early when the registration has been deleted', function () {
    $registration = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
    ]);

    $job = new CompleteDigitalWorkshopRegistration($registration);
    $registration->delete();

    $job->handle(app(QrTokenService::class), app(RegistrationService::class));

    Mail::assertNothingSent();
    expect(DigitalWorkshopRegistration::count())->toBe(0);
});
