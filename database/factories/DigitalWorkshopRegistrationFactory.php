<?php

namespace Database\Factories;

use App\Models\DigitalWorkshop;
use App\Models\DigitalWorkshopRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DigitalWorkshopRegistration>
 */
class DigitalWorkshopRegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'digital_workshop_id' => DigitalWorkshop::factory(),
            'seminar_registration_id' => null,
            'registration_type' => 'standalone',
            'amount' => 1199000,
            'email' => fake()->unique()->safeEmail(),
            'phone' => (string) fake()->numerify('08##########'),
            'name_license' => fake()->name(),
            'nik' => (string) fake()->numerify('################'),
            'pdgi_branch' => fake()->city(),
            'kompetensi' => 'Dokter Gigi Umum',
            'payment_status' => 'pending',
            'payment_method' => 'bank_transfer',
            'payment_proof_path' => 'payment-proofs/test.jpg',
            'language' => 'id',
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
