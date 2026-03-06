<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['name' => 'Indonesia', 'code' => 'IDN', 'is_local' => true, 'phone_code' => '+62'],
            ['name' => 'Singapore', 'code' => 'SGP', 'is_local' => false, 'phone_code' => '+65'],
            ['name' => 'Malaysia', 'code' => 'MYS', 'is_local' => false, 'phone_code' => '+60'],
            ['name' => 'Thailand', 'code' => 'THA', 'is_local' => false, 'phone_code' => '+66'],
            ['name' => 'Philippines', 'code' => 'PHL', 'is_local' => false, 'phone_code' => '+63'],
            ['name' => 'Vietnam', 'code' => 'VNM', 'is_local' => false, 'phone_code' => '+84'],
            ['name' => 'Myanmar', 'code' => 'MMR', 'is_local' => false, 'phone_code' => '+95'],
            ['name' => 'Cambodia', 'code' => 'KHM', 'is_local' => false, 'phone_code' => '+855'],
            ['name' => 'Brunei', 'code' => 'BRN', 'is_local' => false, 'phone_code' => '+673'],
            ['name' => 'Laos', 'code' => 'LAO', 'is_local' => false, 'phone_code' => '+856'],
            ['name' => 'United States', 'code' => 'USA', 'is_local' => false, 'phone_code' => '+1'],
            ['name' => 'United Kingdom', 'code' => 'GBR', 'is_local' => false, 'phone_code' => '+44'],
            ['name' => 'Australia', 'code' => 'AUS', 'is_local' => false, 'phone_code' => '+61'],
            ['name' => 'Japan', 'code' => 'JPN', 'is_local' => false, 'phone_code' => '+81'],
            ['name' => 'South Korea', 'code' => 'KOR', 'is_local' => false, 'phone_code' => '+82'],
            ['name' => 'China', 'code' => 'CHN', 'is_local' => false, 'phone_code' => '+86'],
            ['name' => 'India', 'code' => 'IND', 'is_local' => false, 'phone_code' => '+91'],
            ['name' => 'Germany', 'code' => 'DEU', 'is_local' => false, 'phone_code' => '+49'],
            ['name' => 'France', 'code' => 'FRA', 'is_local' => false, 'phone_code' => '+33'],
            ['name' => 'Netherlands', 'code' => 'NLD', 'is_local' => false, 'phone_code' => '+31'],
            ['name' => 'Canada', 'code' => 'CAN', 'is_local' => false, 'phone_code' => '+1'],
            ['name' => 'New Zealand', 'code' => 'NZL', 'is_local' => false, 'phone_code' => '+64'],
            ['name' => 'Taiwan', 'code' => 'TWN', 'is_local' => false, 'phone_code' => '+886'],
            ['name' => 'Hong Kong', 'code' => 'HKG', 'is_local' => false, 'phone_code' => '+852'],
            ['name' => 'United Arab Emirates', 'code' => 'ARE', 'is_local' => false, 'phone_code' => '+971'],
            ['name' => 'Saudi Arabia', 'code' => 'SAU', 'is_local' => false, 'phone_code' => '+966'],
            ['name' => 'Qatar', 'code' => 'QAT', 'is_local' => false, 'phone_code' => '+974'],
            ['name' => 'Kuwait', 'code' => 'KWT', 'is_local' => false, 'phone_code' => '+965'],
            ['name' => 'Bahrain', 'code' => 'BHR', 'is_local' => false, 'phone_code' => '+973'],
            ['name' => 'Oman', 'code' => 'OMN', 'is_local' => false, 'phone_code' => '+968'],
        ];

        foreach ($countries as $country) {
            Country::firstOrCreate(['code' => $country['code']], $country);
        }
    }
}
