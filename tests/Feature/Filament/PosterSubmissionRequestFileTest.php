<?php

use App\Filament\Resources\PosterSubmissions\Pages\ListPosterSubmissions;
use App\Mail\PosterFileRequest;
use App\Models\PosterCategory;
use App\Models\PosterSubmission;
use App\Models\PosterTopic;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    Storage::fake('public');

    PosterCategory::create(['name' => 'Research', 'slug' => 'research', 'is_active' => true]);
    PosterTopic::create(['name' => 'Implantology', 'slug' => 'implantology', 'is_active' => true]);

    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    actingAs($user);
});

function requestableSubmission(array $attributes = []): PosterSubmission
{
    return PosterSubmission::factory()->create(array_merge([
        'author_emails' => 'author.one@example.com, author.two@example.com',
        'poster_file_path' => null,
    ], $attributes));
}

it('shows the request action when the submission has no poster file', function () {
    $submission = requestableSubmission();

    livewire(ListPosterSubmissions::class)
        ->assertOk()
        ->assertTableActionVisible('requestPosterFile', $submission);
});

it('hides the request action when the poster file exists', function () {
    Storage::disk('public')->put('posters/exists.pdf', '%PDF-1.4 test');

    $submission = requestableSubmission(['poster_file_path' => 'posters/exists.pdf']);

    livewire(ListPosterSubmissions::class)
        ->assertOk()
        ->assertTableActionHidden('requestPosterFile', $submission);
});

it('sends the request email to every valid author address', function () {
    Mail::fake();

    $submission = requestableSubmission();

    livewire(ListPosterSubmissions::class)
        ->callTableAction('requestPosterFile', $submission)
        ->assertNotified();

    Mail::assertQueued(PosterFileRequest::class, 2);
    Mail::assertQueued(PosterFileRequest::class, fn (PosterFileRequest $mail): bool => $mail->hasTo('author.one@example.com'));
    Mail::assertQueued(PosterFileRequest::class, fn (PosterFileRequest $mail): bool => $mail->hasTo('author.two@example.com'));
});

it('skips invalid author addresses and reports when none are usable', function () {
    Mail::fake();

    $submission = requestableSubmission(['author_emails' => 'not-an-email']);

    livewire(ListPosterSubmissions::class)
        ->callTableAction('requestPosterFile', $submission)
        ->assertNotified(__('filament.poster_submissions.request_poster_file_no_recipient'));

    Mail::assertNothingQueued();
});

it('ignores duplicate author addresses', function () {
    Mail::fake();

    $submission = requestableSubmission(['author_emails' => 'author@example.com, author@example.com']);

    livewire(ListPosterSubmissions::class)->callTableAction('requestPosterFile', $submission);

    Mail::assertQueued(PosterFileRequest::class, 1);
});
