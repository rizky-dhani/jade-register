<?php

namespace Database\Factories;

use App\Models\HandsOn;
use App\Models\SeminarRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

class HandsOnRegistrationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'seminar_registration_id' => SeminarRegistration::factory(),
            'hands_on_id' => HandsOn::factory(),
            'registration_type' => 'combined',
            'payment_status' => 'pending',
            'payment_proof_path' => 'payment-proofs/test.jpg',
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'verified',
            'verified_at' => now(),
        ]);
    }
}
