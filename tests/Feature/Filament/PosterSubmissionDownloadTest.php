<?php

use App\Filament\Resources\PosterSubmissions\Actions\DownloadPosterAction;
use App\Filament\Resources\PosterSubmissions\Pages\EditPosterSubmission;
use App\Filament\Resources\PosterSubmissions\Pages\ListPosterSubmissions;
use App\Models\PosterCategory;
use App\Models\PosterSubmission;
use App\Models\PosterTopic;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

function posterWithFile(string $path = 'posters/abstract.pdf'): PosterSubmission
{
    Storage::disk('public')->put($path, '%PDF-1.4 test poster');

    return PosterSubmission::factory()->create([
        'title' => 'Implant Survival Rates',
        'poster_file_path' => $path,
    ]);
}

it('shows the download action for a submission that has a poster file', function () {
    $submission = posterWithFile();

    livewire(ListPosterSubmissions::class)
        ->assertOk()
        ->assertTableActionVisible('downloadPoster', $submission);
});

it('hides the download action when the submission has no poster file', function () {
    $submission = PosterSubmission::factory()->create(['poster_file_path' => null]);

    livewire(ListPosterSubmissions::class)
        ->assertOk()
        ->assertTableActionHidden('downloadPoster', $submission);
});

it('hides the download action when the stored file is gone', function () {
    $submission = PosterSubmission::factory()->create([
        'poster_file_path' => 'posters/missing.pdf',
    ]);

    expect(DownloadPosterAction::hasPosterFile($submission))->toBeFalse();

    livewire(ListPosterSubmissions::class)
        ->assertOk()
        ->assertTableActionHidden('downloadPoster', $submission);
});

it('downloads the poster file from the public disk', function () {
    $submission = posterWithFile();

    livewire(ListPosterSubmissions::class)
        ->callTableAction('downloadPoster', $submission)
        ->assertFileDownloaded();
});

it('offers the download action from the edit page', function () {
    $submission = posterWithFile();

    livewire(EditPosterSubmission::class, ['record' => $submission->getRouteKey()])
        ->assertOk()
        ->assertActionVisible('downloadPoster');
});
