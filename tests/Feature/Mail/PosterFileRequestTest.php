<?php

use App\Mail\PosterFileRequest;
use App\Models\PosterCategory;
use App\Models\PosterSubmission;
use App\Models\PosterTopic;
use App\Models\SeminarRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    PosterCategory::create(['name' => 'Research', 'slug' => 'research', 'is_active' => true]);
    PosterTopic::create(['name' => 'Implantology', 'slug' => 'implantology', 'is_active' => true]);
});

function fileRequestSubmission(array $attributes = []): PosterSubmission
{
    return PosterSubmission::factory()->create(array_merge([
        'title' => 'Implant Survival Rates',
        'presenter_name' => 'Dr. Presenter',
        'author_names' => 'Dr. Author One, Dr. Author Two',
        'affiliation' => 'Universitas Indonesia',
        'poster_file_path' => null,
    ], $attributes));
}

it('builds the envelope with the request subject', function () {
    $mail = new PosterFileRequest(fileRequestSubmission());

    expect($mail->envelope()->subject)->toBe(
        trans('seminar.email_poster_file_request_subject')
    );
});

it('uses the poster file request view', function () {
    $mail = new PosterFileRequest(fileRequestSubmission());

    expect($mail->content()->view)->toBe('emails.poster-file-request');
});

it('renders the submission details and re-upload notice', function () {
    $mail = new PosterFileRequest(fileRequestSubmission());

    $mail->assertSeeInHtml('Implant Survival Rates');
    $mail->assertSeeInHtml('Dr. Presenter');
    $mail->assertSeeInHtml('Dr. Author One, Dr. Author Two');
    $mail->assertSeeInHtml('Universitas Indonesia');
    $mail->assertSeeInHtml(trans('seminar.email_poster_file_request_notice_title'));
    $mail->assertSeeInHtml(route('poster.submit'));
});

it('is queued when dispatched through the mail facade', function () {
    Mail::fake();

    Mail::to('author@example.com')->queue(new PosterFileRequest(fileRequestSubmission()));

    Mail::assertQueued(PosterFileRequest::class);
});

it('localises the content by locale', function () {
    $submission = fileRequestSubmission();
    $registration = SeminarRegistration::find($submission->seminar_registration_id);
    $registration->update(['language' => 'en']);

    $mail = (new PosterFileRequest($submission))->locale('en');

    $mail->assertSeeInHtml(trans('seminar.email_poster_file_request_notice_title', [], 'en'));
});
