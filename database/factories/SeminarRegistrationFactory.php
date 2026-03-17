<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SeminarRegistrationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'registration_code' => 'JADE-SEM-2026-'.str_pad(fake()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->name(),
            'name_license' => fake()->name(),
            'nik' => fake()->numerify('################'),
            'pdgi_branch' => fake()->city(),
            'kompetensi' => fake()->word(),
            'phone' => fake()->phoneNumber(),
            'country_id' => Country::factory(),
            'language' => fake()->randomElement(['en', 'id']),
            'registration_type' => 'online',
            'selected_seminar' => fake()->word(),
            'payment_method' => 'bank_transfer',
            'amount' => fake()->numberBetween(100000, 5000000),
            'currency' => 'IDR',
            'payment_status' => 'pending',
            'payment_proof_path' => 'payment-proofs/test.jpg',
            'wants_poster_competition' => false,
            'wants_hands_on' => false,
            'hands_on_total_amount' => 0,
            'user_id' => User::factory(),
            'qr_token' => Str::random(64),
            'qr_expires_at' => now()->addDays(30),
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'verified',
            'verified_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'pending',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'rejected',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'qr_expires_at' => now()->subDay(),
        ]);
    }
}
