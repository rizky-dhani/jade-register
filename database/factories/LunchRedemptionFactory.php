<?php

namespace Database\Factories;

use App\Models\SeminarRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LunchRedemptionFactory extends Factory
{
    public function definition(): array
    {
        $user = User::factory()->create();

        return [
            'seminar_registration_id' => SeminarRegistration::factory(),
            'redeemed_at' => now(),
            'redeemed_by' => $user->id,
            'redeemed_by_name' => $user->name,
        ];
    }
}
