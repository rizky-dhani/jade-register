<?php

use App\Mail\PosterLetterOfAcceptance;
use App\Models\PosterCategory;
use App\Models\PosterSubmission;
use App\Models\PosterTopic;
use App\Models\SeminarRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');

    PosterCategory::create(['name' => 'Research', 'slug' => 'research', 'is_active' => true]);
    PosterTopic::create(['name' => 'Implantology', 'slug' => 'implantology', 'is_active' => true]);
});

function loaMailSubmission(array $attributes = []): PosterSubmission
{
    return PosterSubmission::factory()->accepted()->create(array_merge([
        'title' => 'Implant Survival Rates',
        'presenter_name' => 'Dr. Presenter',
        'author_names' => 'Dr. Author One, Dr. Author Two',
        'affiliation' => 'Universitas Indonesia',
    ], $attributes));
}

function loaMail(PosterSubmission $submission, array $cc = [], array $bcc = []): PosterLetterOfAcceptance
{
    return new PosterLetterOfAcceptance(
        $submission,
        $cc,
        $bcc,
        'loa-letters/letter.pdf',
        'LoA-JADE-2026-Dr-Presenter.pdf',
    );
}

it('builds the envelope with the LoA subject, cc and bcc', function () {
    $envelope = loaMail(loaMailSubmission(), ['cc@example.com'], ['bcc@example.com'])->envelope();

    expect($envelope->subject)->toBe(trans('seminar.email_poster_loa_subject'))
        ->and($envelope->cc[0]->address)->toBe('cc@example.com')
        ->and($envelope->bcc[0]->address)->toBe('bcc@example.com');
});

it('uses the LoA view', function () {
    expect(loaMail(loaMailSubmission())->content()->view)->toBe('emails.poster-letter-of-acceptance');
});

it('renders the submission details and attachment notice', function () {
    $mail = loaMail(loaMailSubmission());

    $mail->assertSeeInHtml('Implant Survival Rates');
    $mail->assertSeeInHtml('Dr. Presenter');
    $mail->assertSeeInHtml('Dr. Author One, Dr. Author Two');
    $mail->assertSeeInHtml('Universitas Indonesia');
    $mail->assertSeeInHtml(trans('seminar.email_poster_loa_attachment_note'));
});

it('attaches the letter from the local disk', function () {
    Storage::disk('local')->put('loa-letters/letter.pdf', '%PDF-1.4 test');

    loaMail(loaMailSubmission())
        ->assertHasAttachedData(
            Storage::disk('local')->get('loa-letters/letter.pdf'),
            'LoA-JADE-2026-Dr-Presenter.pdf',
            ['mime' => 'application/pdf'],
        );
});

it('localises the content by locale', function () {
    $submission = loaMailSubmission();
    SeminarRegistration::find($submission->seminar_registration_id)->update(['language' => 'en']);

    $mail = loaMail($submission)->locale('en');

    $mail->assertSeeInHtml(trans('seminar.email_poster_loa_attachment_note', [], 'en'));
});
