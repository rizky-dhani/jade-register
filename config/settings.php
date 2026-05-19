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
];
