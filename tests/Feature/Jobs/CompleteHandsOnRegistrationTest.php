<?php

use App\Enums\HandsOnStatus;
use App\Jobs\CompleteHandsOnRegistration;
use App\Mail\HandsOnRegistrationConfirmation;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Services\QrTokenService;
use App\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('generates the hands-on QR token and sends confirmation email', function () {
    Mail::fake();

    $handsOn = HandsOn::create([
        'name' => 'Test Hands On',
        'ho_code' => 'HO-001',
        'doctor_name' => 'Dr. Test',
        'event_date' => '2026-11-13',
        'max_seats' => 10,
        'price' => 500000,
        'original_price' => 500000,
        'currency' => 'IDR',
        'is_active' => true,
        'status' => HandsOnStatus::PUBLISHED,
    ]);

    $registration = HandsOnRegistration::factory()->create([
        'hands_on_id' => $handsOn->id,
        'qr_token' => null,
        'qr_expires_at' => null,
        'confirmation_email_sent_at' => null,
    ]);

    (new CompleteHandsOnRegistration($registration))->handle(
        app(QrTokenService::class),
        app(RegistrationService::class),
    );

    expect($registration->fresh()->qr_token)->not->toBeNull()
        ->and($registration->fresh()->confirmation_email_sent_at)->not->toBeNull();

    Mail::assertSent(HandsOnRegistrationConfirmation::class, fn (HandsOnRegistrationConfirmation $mail): bool => $mail->registration->is($registration));
});

it('does not generate another QR token or resend an already sent confirmation', function () {
    Mail::fake();

    $registration = HandsOnRegistration::factory()->create([
        'qr_token' => str_repeat('a', 64),
        'qr_expires_at' => now()->addDay(),
        'confirmation_email_sent_at' => now(),
    ]);

    (new CompleteHandsOnRegistration($registration))->handle(
        app(QrTokenService::class),
        app(RegistrationService::class),
    );

    expect($registration->fresh()->qr_token)->toBe(str_repeat('a', 64));
    Mail::assertNothingSent();
});
