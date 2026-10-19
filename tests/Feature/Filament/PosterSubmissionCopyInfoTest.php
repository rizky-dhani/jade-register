<?php

use App\Filament\Resources\PosterSubmissions\Actions\CopySubmissionInfoAction;
use App\Filament\Resources\PosterSubmissions\Pages\EditPosterSubmission;
use App\Filament\Resources\PosterSubmissions\Pages\ListPosterSubmissions;
use App\Models\PosterCategory;
use App\Models\PosterSubmission;
use App\Models\PosterTopic;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->category = PosterCategory::create([
        'name' => 'Case Report',
        'slug' => 'case-report',
        'is_active' => true,
    ]);

    $this->topic = PosterTopic::create([
        'name' => 'Implantology',
        'slug' => 'implantology',
        'is_active' => true,
    ]);

    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    actingAs($user);
});

function posterSubmission(): PosterSubmission
{
    return PosterSubmission::factory()->create([
        'title' => 'Implant Survival Rates',
        'poster_category_id' => PosterCategory::where('slug', 'case-report')->value('id'),
        'poster_topic_id' => PosterTopic::where('slug', 'implantology')->value('id'),
        'author_names' => 'Dr. Jane Doe, Dr. John Roe',
        'author_emails' => 'jane@example.com, john@example.com',
        'affiliation' => 'University of Dentistry',
        'presenter_name' => 'Dr. Jane Doe',
    ]);
}

it('builds copy text with the core submission details', function () {
    $submission = posterSubmission();

    $action = CopySubmissionInfoAction::make('copySubmissionInfo');
    $action->record($submission);

    $text = $action->buildSubmissionText($submission);

    expect($text)->toContain('Implant Survival Rates')
        ->and($text)->toContain('Case Report')
        ->and($text)->toContain('Implantology')
        ->and($text)->toContain('Dr. Jane Doe, Dr. John Roe')
        ->and($text)->toContain('jane@example.com, john@example.com')
        ->and($text)->toContain('University of Dentistry')
        ->and($text)->toContain('Dr. Jane Doe');
});

it('falls back to a dash for missing details', function () {
    $submission = new PosterSubmission(['title' => 'Bare Submission']);

    $action = CopySubmissionInfoAction::make('copySubmissionInfo');
    $action->record($submission);

    $text = $action->buildSubmissionText($submission);

    expect($text)->toContain('Bare Submission')
        ->and(substr_count($text, '—'))->toBe(6);
});

it('wires the clipboard handler with the submission details', function () {
    $submission = posterSubmission();

    $action = CopySubmissionInfoAction::make('copySubmissionInfo');
    $action->record($submission);

    $handler = $action->getCustomAlpineClickHandler();

    expect($handler)->not->toBeNull()
        ->and($handler)->toContain('navigator.clipboard.writeText')
        ->and($handler)->toContain('Implant Survival Rates')
        ->and($handler)->toContain('University of Dentistry');
});

it('renders the copy action on the table listing', function () {
    $submission = posterSubmission();

    livewire(ListPosterSubmissions::class)
        ->assertOk()
        ->assertTableActionVisible('copySubmissionInfo', $submission);
});

it('renders the copy action on the edit page', function () {
    $submission = posterSubmission();

    livewire(EditPosterSubmission::class, ['record' => $submission->getRouteKey()])
        ->assertOk()
        ->assertActionVisible('copySubmissionInfo');
});
