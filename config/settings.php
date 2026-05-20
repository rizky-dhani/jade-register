<?php

return [
    'registration_open' => [
        'label' => 'Seminar Registration Open/Close',
        'type' => 'boolean',
        'default' => true,
        'description' => 'Controls whether seminar registration is open to participants. Auto-closes when max participants is reached.',
    ],
    'max_participants' => [
        'label' => 'Seminar Max Participants',
        'type' => 'integer',
        'default' => 500,
        'description' => 'Maximum number of seminar registrations allowed. Automatically closes registration when this limit is reached.',
    ],
    'hands_on_registration_open' => [
        'label' => 'Hands On Registration Open/Close',
        'type' => 'boolean',
        'default' => true,
        'description' => 'Controls whether hands-on registration is open to participants.',
    ],
    'seminar_registration_opens_at' => [
        'label' => 'Seminar Registration Opens At',
        'type' => 'string',
        'default' => null,
        'description' => 'Optional date/time when seminar registration automatically opens. Leave null for no date restriction.',
    ],
    'hands_on_registration_opens_at' => [
        'label' => 'Hands On Registration Opens At',
        'type' => 'string',
        'default' => null,
        'description' => 'Optional date/time when hands-on registration automatically opens. Leave null for no date restriction.',
    ],

    'bank_name' => [
        'label' => 'Bank Name',
        'type' => 'string',
        'default' => env('BANK_NAME', 'Bank BNI'),
        'description' => 'Bank name for bank transfer payments.',
    ],
    'bank_account_name' => [
        'label' => 'Bank Account Name',
        'type' => 'string',
        'default' => env('BANK_ACCOUNT_NAME', ''),
        'description' => 'Bank account holder name for bank transfer payments.',
    ],
    'bank_account_number' => [
        'label' => 'Bank Account Number',
        'type' => 'string',
        'default' => env('BANK_ACCOUNT_NUMBER', ''),
        'description' => 'Bank account number for bank transfer payments.',
    ],
    'bank_swift_code' => [
        'label' => 'Bank SWIFT Code',
        'type' => 'string',
        'default' => env('BANK_SWIFT_CODE', ''),
        'description' => 'SWIFT code for international bank transfer payments.',
    ],
];
