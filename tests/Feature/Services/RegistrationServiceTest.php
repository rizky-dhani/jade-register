<?php

use App\Mail\HandsOnRegistrationConfirmation;
use App\Models\Country;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    Country::factory()->create([
        'name' => 'Indonesia',
        'is_indonesia' => true,
    ]);

    HandsOn::factory()->create([
        'name' => 'Test Hands On Session',
        'ho_code' => 'HO-001',
        'doctor_name' => 'Dr. Test',
        'event_date' => '2026-11-14',
        'price' => 500000,
        'original_price' => 500000,
    ]);
});

describe('sendHandsOnSubmissionConfirmation', function () {
    it('sends confirmation email for a HandsOnRegistration', function () {
        Mail::fake();

        $registration = HandsOnRegistration::factory()->create([
            'email' => 'test@example.com',
            'language' => 'en',
        ]);

        $service = app(RegistrationService::class);
        $service->sendHandsOnSubmissionConfirmation($registration);

        Mail::assertSent(HandsOnRegistrationConfirmation::class, function ($mail) use ($registration) {
            return $mail->registration->is($registration)
                && $mail->hasTo($registration->email);
        });
    });

    it('marks confirmation_email_sent_at after sending', function () {
        Mail::fake();

        $registration = HandsOnRegistration::factory()->create();

        $service = app(RegistrationService::class);
        $service->sendHandsOnSubmissionConfirmation($registration);

        $registration->refresh();
        expect($registration->confirmation_email_sent_at)->not->toBeNull();
    });

    it('skips sending if already sent', function () {
        Mail::fake();

        $registration = HandsOnRegistration::factory()->create([
            'confirmation_email_sent_at' => now()->subHour(),
        ]);

        $service = app(RegistrationService::class);
        $service->sendHandsOnSubmissionConfirmation($registration);

        Mail::assertNothingSent();
    });

    it('sends with correct locale', function () {
        Mail::fake();

        $registration = HandsOnRegistration::factory()->create([
            'language' => 'id',
        ]);

        $service = app(RegistrationService::class);
        $service->sendHandsOnSubmissionConfirmation($registration);

        Mail::assertSent(HandsOnRegistrationConfirmation::class);
    });
});

describe('sendHandsOnAttendanceConfirmation', function () {
    it('sends attendance confirmation email for a HandsOnRegistration', function () {
        Mail::fake();

        $registration = HandsOnRegistration::factory()->create([
            'email' => 'attendance@example.com',
            'language' => 'en',
        ]);

        $service = app(RegistrationService::class);
        $service->sendHandsOnAttendanceConfirmation($registration);

        Mail::assertSent(HandsOnRegistrationConfirmation::class, function ($mail) use ($registration) {
            return $mail->registration->is($registration)
                && $mail->hasTo($registration->email);
        });
    });

    it('marks confirmation_email_sent_at after sending attendance confirmation', function () {
        Mail::fake();

        $registration = HandsOnRegistration::factory()->create();

        $service = app(RegistrationService::class);
        $service->sendHandsOnAttendanceConfirmation($registration);

        $registration->refresh();
        expect($registration->confirmation_email_sent_at)->not->toBeNull();
    });

    it('skips attendance confirmation if already sent', function () {
        Mail::fake();

        $registration = HandsOnRegistration::factory()->create([
            'confirmation_email_sent_at' => now()->subHour(),
        ]);

        $service = app(RegistrationService::class);
        $service->sendHandsOnAttendanceConfirmation($registration);

        Mail::assertNothingSent();
    });
});
