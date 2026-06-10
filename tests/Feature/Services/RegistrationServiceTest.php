<?php

use App\Mail\HandsOnRegistrationConfirmation;
use App\Mail\PosterSubmissionConfirmation;
use App\Models\Country;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\PosterCategory;
use App\Models\PosterSubmission;
use App\Models\PosterTopic;
use App\Models\SeminarRegistration;
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

    it('resends if already sent', function () {
        Mail::fake();

        $registration = HandsOnRegistration::factory()->create([
            'confirmation_email_sent_at' => now()->subHour(),
        ]);

        $service = app(RegistrationService::class);
        $service->sendHandsOnSubmissionConfirmation($registration);

        Mail::assertSent(HandsOnRegistrationConfirmation::class);
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

    it('resends attendance confirmation if already sent', function () {
        Mail::fake();

        $registration = HandsOnRegistration::factory()->create([
            'confirmation_email_sent_at' => now()->subHour(),
        ]);

        $service = app(RegistrationService::class);
        $service->sendHandsOnAttendanceConfirmation($registration);

        Mail::assertSent(HandsOnRegistrationConfirmation::class);
    });
});

describe('sendPosterSubmissionConfirmation', function () {
    it('sends confirmation email to all author emails', function () {
        Mail::fake();

        $seminarRegistration = SeminarRegistration::factory()->verified()->create();

        $posterCategory = PosterCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true,
        ]);

        $posterTopic = PosterTopic::create([
            'name' => 'Test Topic',
            'slug' => 'test-topic',
            'is_active' => true,
        ]);

        $submission = PosterSubmission::create([
            'seminar_registration_id' => $seminarRegistration->id,
            'poster_category_id' => $posterCategory->id,
            'poster_topic_id' => $posterTopic->id,
            'title' => 'Test Title',
            'abstract_text' => 'Test abstract',
            'author_names' => 'Author One, Author Two',
            'author_emails' => 'author1@test.com, author2@test.com',
            'affiliation' => 'Test University',
            'presenter_name' => 'Author One',
            'status' => PosterSubmission::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $service = app(RegistrationService::class);
        $service->sendPosterSubmissionConfirmation($submission);

        Mail::assertSent(PosterSubmissionConfirmation::class, 2);
    });

    it('sends to single email when only one author', function () {
        Mail::fake();

        $seminarRegistration = SeminarRegistration::factory()->verified()->create();

        $posterCategory = PosterCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true,
        ]);

        $posterTopic = PosterTopic::create([
            'name' => 'Test Topic',
            'slug' => 'test-topic',
            'is_active' => true,
        ]);

        $submission = PosterSubmission::create([
            'seminar_registration_id' => $seminarRegistration->id,
            'poster_category_id' => $posterCategory->id,
            'poster_topic_id' => $posterTopic->id,
            'title' => 'Test Title',
            'abstract_text' => 'Test abstract',
            'author_names' => 'Single Author',
            'author_emails' => 'single@test.com',
            'affiliation' => 'Test University',
            'presenter_name' => 'Single Author',
            'status' => PosterSubmission::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $service = app(RegistrationService::class);
        $service->sendPosterSubmissionConfirmation($submission);

        Mail::assertSent(PosterSubmissionConfirmation::class, 1);
    });

    it('skips invalid email addresses', function () {
        Mail::fake();

        $seminarRegistration = SeminarRegistration::factory()->verified()->create();

        $posterCategory = PosterCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true,
        ]);

        $posterTopic = PosterTopic::create([
            'name' => 'Test Topic',
            'slug' => 'test-topic',
            'is_active' => true,
        ]);

        $submission = PosterSubmission::create([
            'seminar_registration_id' => $seminarRegistration->id,
            'poster_category_id' => $posterCategory->id,
            'poster_topic_id' => $posterTopic->id,
            'title' => 'Test Title',
            'abstract_text' => 'Test abstract',
            'author_names' => 'Valid Author',
            'author_emails' => 'valid@test.com, not-an-email',
            'affiliation' => 'Test University',
            'presenter_name' => 'Valid Author',
            'status' => PosterSubmission::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $service = app(RegistrationService::class);
        $service->sendPosterSubmissionConfirmation($submission);

        Mail::assertSent(PosterSubmissionConfirmation::class, 1);
    });
});
