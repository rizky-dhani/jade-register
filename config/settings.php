<?php

return [
    'bank_name' => env('BANK_NAME', 'Bank Central Asia (BCA)'),
    'bank_account_name' => env('BANK_ACCOUNT_NAME', 'PT Jakarta Dental Exhibition'),
    'bank_account_number' => env('BANK_ACCOUNT_NUMBER', '1234567890'),
    'payment_instructions' => env('PAYMENT_INSTRUCTIONS', 'Please transfer the exact amount to the bank account above.'),
    'event_terms_conditions' => '',
    'venue_name' => env('VENUE_NAME', 'Jakarta Convention Center'),
    'venue_address' => env('VENUE_ADDRESS', 'Jl. Gatot Subroto, Jakarta Pusat, Indonesia'),
    'venue_latitude' => env('VENUE_LATITUDE', '-6.2147245'),
    'venue_longitude' => env('VENUE_LONGITUDE', '106.8073332'),
    'venue_detection_radius' => (int) env('VENUE_DETECTION_RADIUS', 500),
];
