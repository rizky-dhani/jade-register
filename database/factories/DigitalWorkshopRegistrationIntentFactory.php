<?php

namespace Database\Factories;

use App\Models\DigitalWorkshop;
use App\Models\DigitalWorkshopRegistrationIntent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DigitalWorkshopRegistrationIntent>
 */
class DigitalWorkshopRegistrationIntentFactory extends Factory
{
    protected $model = DigitalWorkshopRegistrationIntent::class;

    public function definition(): array
    {
        return [
            'digital_workshop_id' => DigitalWorkshop::factory(),
            'status' => 'awaiting_seminar',
            'email' => fake()->unique()->safeEmail(),
            'name' => null,
            'name_license' => 'Dr. '.fake()->name(),
            'nik' => (string) fake()->numerify('################'),
            'pdgi_branch' => fake()->city(),
            'phone' => (string) fake()->numerify('08##########'),
            'kompetensi' => 'Dokter Gigi Umum',
            'status_participant' => null,
            'country_id' => null,
            'payment_method' => 'bank_transfer',
            'payment_proof_path' => null,
            'language' => 'id',
        ];
    }
}
