<?php

namespace Database\Factories;

use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'seminar_registration_id' => SeminarRegistration::factory(),
            'hands_on_registration_id' => null,
            'activity_type' => 'seminar',
            'checked_in_at' => now(),
            'checked_in_by' => User::factory(),
            'notes' => null,
        ];
    }

    public function handsOn(): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_type' => 'hands_on',
            'hands_on_registration_id' => HandsOnRegistration::factory(),
        ]);
    }
}
