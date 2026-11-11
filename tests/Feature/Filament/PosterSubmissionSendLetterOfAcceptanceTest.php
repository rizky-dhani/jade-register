<?php

use App\Filament\Resources\PosterSubmissions\Pages\ListPosterSubmissions;
use App\Mail\PosterLetterOfAcceptance;
use App\Models\PosterCategory;
use App\Models\PosterSubmission;
use App\Models\PosterTopic;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    Storage::fake('local');

    PosterCategory::create(['name' => 'Research', 'slug' => 'research', 'is_active' => true]);
    PosterTopic::create(['name' => 'Implantology', 'slug' => 'implantology', 'is_active' => true]);

    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    actingAs($user);
});

function loaSubmission(array $attributes = []): PosterSubmission
{
    return PosterSubmission::factory()->accepted()->create(array_merge([
        'title' => 'Implant Survival Rates',
        'presenter_name' => 'Dr. Presenter',
        'author_names' => 'Dr. Author One, Dr. Author Two',
        'author_emails' => 'author.one@example.com, author.two@example.com',
        'affiliation' => 'Universitas Indonesia',
    ], $attributes));
}

/**
 * Invoke the action closure directly: callTableAction() routes data through
 * fillForm(), which silently no-ops in this app (see .ai/rules/filament.md).
 */
function callLoaAction(PosterSubmission $submission, array $data): void
{
    livewire(ListPosterSubmissions::class)
        ->instance()
        ->getTable()
        ->getAction('sendLetterOfAcceptance')
        ->call(['record' => $submission, 'data' => $data]);
}

it('shows the action for every submission status', function (string $status) {
    $submission = loaSubmission(['status' => $status]);

    livewire(ListPosterSubmissions::class)
        ->assertOk()
        ->assertTableActionVisible('sendLetterOfAcceptance', $submission);
})->with([
    PosterSubmission::STATUS_DRAFT,
    PosterSubmission::STATUS_SUBMITTED,
    PosterSubmission::STATUS_UNDER_REVIEW,
    PosterSubmission::STATUS_ACCEPTED,
    PosterSubmission::STATUS_FINALIST,
    PosterSubmission::STATUS_WINNER,
    PosterSubmission::STATUS_REJECTED,
]);

it('hides the action without the update permission', function () {
    $submission = loaSubmission();

    $viewer = User::factory()->create();
    $viewer->givePermissionTo('view poster submissions');
    actingAs($viewer);

    livewire(ListPosterSubmissions::class)
        ->assertOk()
        ->assertTableActionHidden('sendLetterOfAcceptance', $submission);
});

it('prefills the To field with the author emails', function () {
    $submission = loaSubmission();

    livewire(ListPosterSubmissions::class)
        ->mountTableAction('sendLetterOfAcceptance', $submission)
        ->assertTableActionDataSet([
            'to' => ['author.one@example.com', 'author.two@example.com'],
        ]);
});

it('queues the LoA email with cc, bcc and the uploaded letter', function () {
    Mail::fake();

    Storage::disk('local')->put('loa-letters/loa-1.pdf', '%PDF-1.4 test');

    $submission = loaSubmission();

    callLoaAction($submission, [
        'to' => ['author.one@example.com', 'author.two@example.com'],
        'cc' => ['cc@example.com'],
        'bcc' => ['bcc@example.com'],
        'letter_path' => 'loa-letters/loa-1.pdf',
    ]);

    Mail::assertQueued(PosterLetterOfAcceptance::class, 1);
    Mail::assertQueued(PosterLetterOfAcceptance::class, fn (PosterLetterOfAcceptance $mail): bool => $mail->hasTo('author.one@example.com')
        && $mail->hasTo('author.two@example.com')
        && $mail->hasCc('cc@example.com')
        && $mail->hasBcc('bcc@example.com')
        && $mail->letterPath === 'loa-letters/loa-1.pdf'
        && $mail->letterName === 'LoA-JADE-2026-dr-presenter.pdf');

    Notification::assertNotified(__('filament.poster_submissions.loa_sent', ['count' => 2]));
});

it('skips invalid recipients and reports when none are usable', function () {
    Mail::fake();

    $submission = loaSubmission();

    callLoaAction($submission, [
        'to' => ['not-an-email'],
        'letter_path' => 'loa-letters/loa-1.pdf',
    ]);

    Mail::assertNothingQueued();

    Notification::assertNotified(__('filament.poster_submissions.loa_no_recipient'));
});
