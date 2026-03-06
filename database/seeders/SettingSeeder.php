<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'bank_account_name' => 'PT Jakarta Dental Exhibition',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Bank Central Asia (BCA)',
            'payment_instructions' => 'Please transfer the registration fee to the bank account above. Upload your payment proof after transferring.',
            'event_terms_conditions' => '',
            'venue_name' => 'Jakarta Convention Center',
            'venue_address' => 'Jl. Gatot Subroto, Jakarta Pusat, Indonesia',
            'venue_latitude' => '-6.2147245',
            'venue_longitude' => '106.8073332',
            'venue_detection_radius' => '500',
        ];

        foreach ($settings as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
