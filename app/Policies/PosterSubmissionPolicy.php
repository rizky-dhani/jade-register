<?php

namespace App\Policies;

use App\Models\PosterSubmission;
use App\Models\User;

class PosterSubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view poster submissions');
    }

    public function view(User $user, PosterSubmission $posterSubmission): bool
    {
        return $user->can('view poster submissions')
            || $user->getKey() === $posterSubmission->user_id;
    }

    public function create(User $user): bool
    {
        return $user->can('create poster submissions');
    }

    public function update(User $user, PosterSubmission $posterSubmission): bool
    {
        return $user->can('update poster submissions')
            || $user->getKey() === $posterSubmission->user_id;
    }

    public function delete(User $user, PosterSubmission $posterSubmission): bool
    {
        return $user->can('delete poster submissions')
            || $user->getKey() === $posterSubmission->user_id;
    }

    public function restore(User $user, PosterSubmission $posterSubmission): bool
    {
        return $user->can('manage poster submissions');
    }

    public function forceDelete(User $user, PosterSubmission $posterSubmission): bool
    {
        return $user->can('manage poster submissions');
    }
}
