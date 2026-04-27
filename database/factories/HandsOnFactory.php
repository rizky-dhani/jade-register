<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class HandsOnFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'ho_code' => 'HO-'.strtoupper(fake()->bothify('??###')),
            'doctor_name' => fake()->name('male'),
            'description' => fake()->paragraph(),
            'event_date' => fake()->dateTimeBetween('2026-11-13', '2026-11-15')->format('Y-m-d'),
            'max_seats' => fake()->numberBetween(10, 100),
            'price' => fake()->numberBetween(100000, 1000000),
            'original_price' => fake()->numberBetween(100000, 1000000),
            'discounted_price' => null,
            'early_bird_deadline' => null,
            'currency' => 'IDR',
            'is_active' => true,
        ];
    }
}
