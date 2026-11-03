<?php

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

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');
    actingAs($admin);

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
});

it('shows only the first author plus a count in the penulis column', function () {
    $submission = PosterSubmission::factory()->create([
        'poster_category_id' => $this->category->getKey(),
        'poster_topic_id' => $this->topic->getKey(),
        'author_names' => 'Aleina Indira, Irene Patricia, Abigail Marisca Christy, Alifia Dian Pramesti',
    ]);

    livewire(ListPosterSubmissions::class)
        ->assertOk()
        ->assertTableColumnExists('author_names')
        ->assertTableColumnStateSet('author_names', 'Aleina Indira, Irene Patricia, Abigail Marisca Christy, Alifia Dian Pramesti', $submission)
        ->assertTableColumnFormattedStateSet('author_names', 'Aleina Indira + 3', $submission);
});

it('shows a sole author without a count', function () {
    $submission = PosterSubmission::factory()->create([
        'poster_category_id' => $this->category->getKey(),
        'poster_topic_id' => $this->topic->getKey(),
        'author_names' => 'Zakiyaturohmah',
    ]);

    livewire(ListPosterSubmissions::class)
        ->assertOk()
        ->assertTableColumnFormattedStateSet('author_names', 'Zakiyaturohmah', $submission);
});

it('keeps the full author list in the column tooltip', function () {
    $submission = PosterSubmission::factory()->create([
        'poster_category_id' => $this->category->getKey(),
        'poster_topic_id' => $this->topic->getKey(),
        'author_names' => 'Elin Hertiana, Sarah Mersil',
    ]);

    livewire(ListPosterSubmissions::class)
        ->assertOk()
        ->assertTableColumnExists('author_names', fn ($column): bool => $column->getTooltip($submission) === 'Elin Hertiana, Sarah Mersil');
});

it('labels the penulis column with the author translation', function () {
    livewire(ListPosterSubmissions::class)
        ->assertOk()
        ->assertTableColumnExists('author_names', fn ($column): bool => $column->getLabel() === __('filament.poster_submissions.author'));
});

it('no longer shows a score column', function () {
    livewire(ListPosterSubmissions::class)
        ->assertOk()
        ->assertTableColumnDoesNotExist('average_score');
});

it('still searches the full author list, not just the shown author', function () {
    $submission = PosterSubmission::factory()->create([
        'poster_category_id' => $this->category->getKey(),
        'poster_topic_id' => $this->topic->getKey(),
        'author_names' => 'Aleina Indira, Irene Patricia',
    ]);

    livewire(ListPosterSubmissions::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$submission])
        ->searchTable('Irene Patricia')
        ->assertCanSeeTableRecords([$submission]);
});

it('sorts the newest submission first by default', function () {
    $older = PosterSubmission::factory()->create([
        'poster_category_id' => $this->category->getKey(),
        'poster_topic_id' => $this->topic->getKey(),
        'submitted_at' => now()->subDays(2),
    ]);

    $newer = PosterSubmission::factory()->create([
        'poster_category_id' => $this->category->getKey(),
        'poster_topic_id' => $this->topic->getKey(),
        'submitted_at' => now()->subDay(),
    ]);

    livewire(ListPosterSubmissions::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$newer, $older], inOrder: true);
});

it('lets the committee reverse the submitted column order', function () {
    $older = PosterSubmission::factory()->create([
        'poster_category_id' => $this->category->getKey(),
        'poster_topic_id' => $this->topic->getKey(),
        'submitted_at' => now()->subDays(2),
    ]);

    $newer = PosterSubmission::factory()->create([
        'poster_category_id' => $this->category->getKey(),
        'poster_topic_id' => $this->topic->getKey(),
        'submitted_at' => now()->subDay(),
    ]);

    livewire(ListPosterSubmissions::class)
        ->sortTable('submitted_at', 'asc')
        ->assertCanSeeTableRecords([$older, $newer], inOrder: true);
});

it('keeps drafts with no submitted date out of the way of the newest first order', function () {
    $submitted = PosterSubmission::factory()->create([
        'poster_category_id' => $this->category->getKey(),
        'poster_topic_id' => $this->topic->getKey(),
        'submitted_at' => now()->subDay(),
    ]);

    $draft = PosterSubmission::factory()->create([
        'poster_category_id' => $this->category->getKey(),
        'poster_topic_id' => $this->topic->getKey(),
        'status' => PosterSubmission::STATUS_DRAFT,
        'submitted_at' => null,
    ]);

    livewire(ListPosterSubmissions::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$submitted, $draft], inOrder: true);
});
