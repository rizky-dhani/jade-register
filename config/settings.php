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
        'type' => 'datetime',
        'default' => null,
        'description' => 'Optional date/time when seminar registration automatically opens. Leave null for no date restriction.',
    ],
    'seminar_registration_close_at' => [
        'label' => 'Seminar Registration Close At',
        'type' => 'datetime',
        'default' => null,
        'description' => 'Optional date/time when seminar registration automatically closes. Leave null for no date restriction.',
    ],
    'hands_on_registration_opens_at' => [
        'label' => 'Hands On Registration Opens At',
        'type' => 'datetime',
        'default' => null,
        'description' => 'Optional date/time when hands-on registration automatically opens. Leave null for no date restriction.',
    ],
    'hands_on_registration_close_at' => [
        'label' => 'Hands On Registration Close At',
        'type' => 'datetime',
        'default' => null,
        'description' => 'Optional date/time when hands-on registration automatically closes. Leave null for no date restriction.',
    ],

    'digital_workshop_registration_open' => [
        'label' => 'Digital Workshop Registration Open/Close',
        'type' => 'boolean',
        'default' => true,
        'description' => 'Controls whether digital workshop registration is open to participants.',
    ],
    'digital_workshop_registration_opens_at' => [
        'label' => 'Digital Workshop Registration Opens At',
        'type' => 'datetime',
        'default' => null,
        'description' => 'Optional date/time when digital workshop registration automatically opens. Leave null for no date restriction.',
    ],
    'digital_workshop_registration_close_at' => [
        'label' => 'Digital Workshop Registration Close At',
        'type' => 'datetime',
        'default' => null,
        'description' => 'Optional date/time when digital workshop registration automatically closes. Leave null for no date restriction.',
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

    'whatsapp_group_url' => [
        'label' => 'WhatsApp Group URL',
        'type' => 'string',
        'default' => env('WHATSAPP_GROUP_URL', 'https://chat.whatsapp.com/FOUtwzgjBodABp1TdsEQcH?s=cl&p=a&mlu=4&ilr=4'),
        'description' => 'Invite link for the participant WhatsApp group. Used by the seminar/hands-on confirmation emails and the registration success pages.',
    ],
];
