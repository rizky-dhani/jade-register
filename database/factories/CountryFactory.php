<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CountryFactory extends Factory
{
    public function definition(): array
    {
        // Deterministic codes avoid colliding with tests that create
        // countries manually (e.g. Indonesia 'ID') and each other.
        return [
            'name' => 'Test Country '.fake()->unique()->numberBetween(1, 9999),
            'code' => 'CF'.str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'is_indonesia' => false,
            'phone_code' => '+'.fake()->unique()->numberBetween(1, 999),
        ];
    }

    public function indonesia(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Indonesia',
            'code' => 'ID',
            'is_indonesia' => true,
            'phone_code' => '+62',
        ]);
    }

    public function international(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_indonesia' => false,
        ]);
    }
}
