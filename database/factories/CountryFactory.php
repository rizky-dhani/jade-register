<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CountryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->country(),
            'code' => fake()->countryCode(),
            'is_indonesia' => false,
            'phone_code' => '+'.fake()->numberBetween(1, 999),
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
}
