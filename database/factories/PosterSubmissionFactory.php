<?php

namespace Database\Factories;

use App\Models\PosterCategory;
use App\Models\PosterSubmission;
use App\Models\PosterTopic;
use App\Models\SeminarRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PosterSubmissionFactory extends Factory
{
    protected $model = PosterSubmission::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'seminar_registration_id' => SeminarRegistration::factory(),
            'poster_category_id' => PosterCategory::query()->inRandomOrder()->first()?->id ?? PosterCategory::factory(),
            'poster_topic_id' => PosterTopic::query()->inRandomOrder()->first()?->id ?? PosterTopic::factory(),
            'title' => fake()->sentence(3),
            'abstract_text' => fake()->paragraphs(3, true),
            'author_names' => fake()->name().', '.fake()->name(),
            'author_emails' => fake()->email().', '.fake()->email(),
            'affiliation' => fake()->company(),
            'presenter_name' => fake()->name(),
            'status' => PosterSubmission::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PosterSubmission::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);
    }

    public function underReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PosterSubmission::STATUS_UNDER_REVIEW,
            'submitted_at' => now()->subDay(),
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PosterSubmission::STATUS_ACCEPTED,
            'submitted_at' => now()->subDays(3),
            'total_score' => fake()->numberBetween(70, 95),
        ]);
    }

    public function winner(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PosterSubmission::STATUS_WINNER,
            'submitted_at' => now()->subDays(5),
            'total_score' => fake()->numberBetween(85, 100),
            'rank' => 1,
        ]);
    }
}
