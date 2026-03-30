<?php

namespace App\Policies;

use App\Models\PosterEvaluation;
use App\Models\User;

class PosterEvaluationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage poster submissions')
            || $user->can('view poster submissions');
    }

    public function view(User $user, PosterEvaluation $posterEvaluation): bool
    {
        return $user->can('manage poster submissions')
            || $user->getKey() === $posterEvaluation->judge_id;
    }

    public function create(User $user): bool
    {
        return $user->can('evaluate poster submissions');
    }

    public function update(User $user, PosterEvaluation $posterEvaluation): bool
    {
        return $user->can('evaluate poster submissions')
            || $user->getKey() === $posterEvaluation->judge_id;
    }

    public function delete(User $user, PosterEvaluation $posterEvaluation): bool
    {
        return $user->can('manage poster submissions');
    }

    public function restore(User $user, PosterEvaluation $posterEvaluation): bool
    {
        return $user->can('manage poster submissions');
    }

    public function forceDelete(User $user, PosterEvaluation $posterEvaluation): bool
    {
        return $user->can('manage poster submissions');
    }
}
