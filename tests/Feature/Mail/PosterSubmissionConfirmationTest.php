<?php

use App\Mail\PosterSubmissionConfirmation;
use App\Models\Country;
use App\Models\PosterCategory;
use App\Models\PosterSubmission;
use App\Models\PosterTopic;
use App\Models\SeminarRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->country = Country::factory()->indonesia()->create();

    PosterCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    PosterTopic::create([
        'name' => 'Test Topic',
        'slug' => 'test-topic',
        'is_active' => true,
    ]);
});

it('builds envelope with correct subject', function () {
    $registration = SeminarRegistration::factory()->verified()->create([
        'country_id' => $this->country->id,
    ]);
    $submission = PosterSubmission::factory()->create([
        'seminar_registration_id' => $registration->id,
    ]);

    $mail = new PosterSubmissionConfirmation($submission);
    $envelope = $mail->envelope();

    expect($envelope->subject)->toBe(
        trans('seminar.email_poster_submission_confirmation_subject')
    );
});

it('has correct view', function () {
    $registration = SeminarRegistration::factory()->verified()->create([
        'country_id' => $this->country->id,
    ]);
    $submission = PosterSubmission::factory()->create([
        'seminar_registration_id' => $registration->id,
    ]);

    $mail = new PosterSubmissionConfirmation($submission);
    $content = $mail->content();

    expect($content->view)->toBe('emails.poster-submission-confirmation');
});

it('renders email with submission details', function () {
    $registration = SeminarRegistration::factory()->verified()->create([
        'country_id' => $this->country->id,
    ]);
    $submission = PosterSubmission::factory()->create([
        'seminar_registration_id' => $registration->id,
        'title' => 'Test Poster Title',
        'presenter_name' => 'Dr. Presenter',
        'author_names' => 'Dr. Author One, Dr. Author Two',
    ]);

    $mail = new PosterSubmissionConfirmation($submission);

    $mail->assertSeeInHtml('Test Poster Title');
    $mail->assertSeeInHtml('Dr. Presenter');
    $mail->assertSeeInHtml('Dr. Author One, Dr. Author Two');
});

it('can be sent via mail facade', function () {
    Mail::fake();

    $registration = SeminarRegistration::factory()->verified()->create([
        'country_id' => $this->country->id,
    ]);
    $submission = PosterSubmission::factory()->create([
        'seminar_registration_id' => $registration->id,
    ]);

    Mail::to('test@example.com')->send(new PosterSubmissionConfirmation($submission));

    Mail::assertSent(PosterSubmissionConfirmation::class);
});
