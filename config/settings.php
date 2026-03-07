<?php

return [
    'venue_name' => env('VENUE_NAME', 'Jakarta International Expo'),
    'venue_address' => env('VENUE_ADDRESS', 'Jl. Expo Kemayoran, Jakarta Pusat'),
    'venue_latitude' => (float) env('VENUE_LATITUDE', -6.2147245),
    'venue_longitude' => (float) env('VENUE_LONGITUDE', 106.8073332),
    'venue_detection_radius' => (int) env('VENUE_DETECTION_RADIUS', 500),
    'bank_name' => env('BANK_NAME', 'Bank Central Asia (BCA)'),
    'bank_account_name' => env('BANK_ACCOUNT_NAME', 'PT Jakarta Dental Exhibition'),
    'bank_account_number' => env('BANK_ACCOUNT_NUMBER', '1234567890'),
    'bank_swift_code' => env('BANK_SWIFT_CODE', 'CENAIDJA'),
    'payment_instructions' => env('PAYMENT_INSTRUCTIONS', 'Please transfer the exact amount to the bank account above.'),
    'event_terms_conditions' => '',
];
