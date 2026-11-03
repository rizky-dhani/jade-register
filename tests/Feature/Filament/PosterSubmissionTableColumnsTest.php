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

it('shows the author names in the penulis column', function () {
    $submission = PosterSubmission::factory()->create([
        'poster_category_id' => $this->category->getKey(),
        'poster_topic_id' => $this->topic->getKey(),
        'author_names' => 'Dr. Jane Doe, Dr. John Roe',
    ]);

    livewire(ListPosterSubmissions::class)
        ->assertOk()
        ->assertTableColumnExists('author_names')
        ->assertTableColumnStateSet('author_names', 'Dr. Jane Doe, Dr. John Roe', $submission)
        ->assertTableColumnFormattedStateSet('author_names', 'Dr. Jane Doe, Dr. John Roe', $submission);
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
