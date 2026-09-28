<?php

use App\Jobs\CompleteSeminarRegistration;
use App\Mail\SeminarRegistrationConfirmation;
use App\Models\SeminarRegistration;
use App\Services\QrTokenService;
use App\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('generates the registration QR token and sends confirmation email', function () {
    Mail::fake();

    $registration = SeminarRegistration::factory()->create([
        'email' => 'registrant@example.com',
        'qr_token' => null,
        'qr_expires_at' => null,
        'confirmation_email_sent_at' => null,
    ]);
    $job = new CompleteSeminarRegistration($registration);
    $job->handle(app(QrTokenService::class), app(RegistrationService::class));

    expect($registration->fresh()->qr_token)->not->toBeNull()
        ->and($registration->fresh()->confirmation_email_sent_at)->not->toBeNull();

    Mail::assertSent(SeminarRegistrationConfirmation::class, fn (SeminarRegistrationConfirmation $mail): bool => $mail->registration->is($registration));
});

it('does not generate a second token or resend an already sent confirmation', function () {
    Mail::fake();

    $registration = SeminarRegistration::factory()->create([
        'qr_token' => str_repeat('a', 64),
        'qr_expires_at' => now()->addDay(),
        'confirmation_email_sent_at' => now(),
    ]);

    $job = new CompleteSeminarRegistration($registration);
    $job->handle(app(QrTokenService::class), app(RegistrationService::class));

    expect($registration->fresh()->qr_token)->toBe(str_repeat('a', 64));
    Mail::assertNothingSent();
});
