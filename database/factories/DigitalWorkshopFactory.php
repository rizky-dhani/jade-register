<?php

namespace Database\Factories;

use App\Models\DigitalWorkshop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DigitalWorkshop>
 */
class DigitalWorkshopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'DW-'.strtoupper(fake()->bothify('??###')),
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'event_date' => '2026-11-20',
            'event_time' => '09:00:00',
            'event_end_time' => '12:00:00',
            'location' => fake()->city(),
            'price' => 1199000,
            'bundle_price' => 999000,
            'max_seats' => fake()->numberBetween(10, 100),
            'status' => 'draft',
            'sort_order' => 0,
        ];
    }
}
