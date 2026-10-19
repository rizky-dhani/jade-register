<?php

use App\Filament\Resources\PosterSubmissions\Pages\EditPosterSubmission;
use App\Filament\Resources\PosterSubmissions\RelationManagers\PosterEvaluationsRelationManager;
use App\Models\PosterCategory;
use App\Models\PosterEvaluation;
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
});

function relationManagerSubmission(): PosterSubmission
{
    return PosterSubmission::factory()->create([
        'poster_category_id' => PosterCategory::where('slug', 'case-report')->value('id'),
        'poster_topic_id' => PosterTopic::where('slug', 'implantology')->value('id'),
    ]);
}

it('renders the evaluations relation manager on the edit page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');
    actingAs($admin);

    $submission = relationManagerSubmission();

    livewire(PosterEvaluationsRelationManager::class, [
        'ownerRecord' => $submission,
        'pageClass' => EditPosterSubmission::class,
    ])
        ->assertOk()
        ->assertSuccessful();
});

it('shows the create evaluation action to a judge', function () {
    $judge = User::factory()->create();
    $judge->assignRole('Judge');
    actingAs($judge);

    $submission = relationManagerSubmission();

    livewire(PosterEvaluationsRelationManager::class, [
        'ownerRecord' => $submission,
        'pageClass' => EditPosterSubmission::class,
    ])
        ->assertOk()
        ->assertTableActionVisible('create');
});

it('only lists the current judge evaluations for non super admins', function () {
    $judge = User::factory()->create();
    $judge->assignRole('Judge');

    $otherJudge = User::factory()->create();

    $submission = relationManagerSubmission();

    $own = PosterEvaluation::create([
        'poster_submission_id' => $submission->getKey(),
        'judge_id' => $judge->getKey(),
        'content_score' => 10,
        'creativity_score' => 5,
        'visual_score' => 5,
        'presentation_score' => 5,
        'evaluated_at' => now(),
    ]);

    $foreign = PosterEvaluation::create([
        'poster_submission_id' => $submission->getKey(),
        'judge_id' => $otherJudge->getKey(),
        'content_score' => 20,
        'creativity_score' => 10,
        'visual_score' => 10,
        'presentation_score' => 10,
        'evaluated_at' => now(),
    ]);

    actingAs($judge);

    livewire(PosterEvaluationsRelationManager::class, [
        'ownerRecord' => $submission,
        'pageClass' => EditPosterSubmission::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords([$own])
        ->assertCanNotSeeTableRecords([$foreign]);
});
