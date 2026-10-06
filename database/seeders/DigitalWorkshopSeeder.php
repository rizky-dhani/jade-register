<?php

namespace Database\Seeders;

use App\Enums\HandsOnStatus;
use App\Models\DigitalWorkshop;
use Illuminate\Database\Seeder;

class DigitalWorkshopSeeder extends Seeder
{
    public function run(): void
    {
        DigitalWorkshop::firstOrCreate(
            ['code' => 'DW-01'],
            [
                'name' => 'Digital Workshop',
                'description' => 'A practical session on building and reading your own social media presence.',
                'event_date' => '2026-11-21',
                'event_time' => '09:00:00',
                'event_end_time' => '12:00:00',
                'location' => 'Jakarta',
                'price' => 1199000,
                'bundle_price' => 999000,
                'max_seats' => null,
                'status' => HandsOnStatus::DRAFT->value,
                'sort_order' => 0,
            ],
        );
    }
}
