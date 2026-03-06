<?php

use App\Models\Setting;

return [
    'bank_name' => Setting::get('bank_name', 'Bank Central Asia (BCA)'),
    'bank_account_name' => Setting::get('bank_account_name', 'PT Jakarta Dental Exhibition'),
    'bank_account_number' => Setting::get('bank_account_number', '1234567890'),
    'payment_instructions' => Setting::get('payment_instructions', 'Please transfer the exact amount to the bank account above.'),
    'event_terms_conditions' => Setting::get('event_terms_conditions', ''),
    'venue_name' => Setting::get('venue_name', 'Jakarta Convention Center'),
    'venue_address' => Setting::get('venue_address', 'Jl. Gatot Subroto, Jakarta Pusat, Indonesia'),
    'venue_latitude' => Setting::get('venue_latitude', '-6.2147245'),
    'venue_longitude' => Setting::get('venue_longitude', '106.8073332'),
    'venue_detection_radius' => Setting::get('venue_detection_radius', 500),
];
