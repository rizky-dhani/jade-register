<?php

return [
    // Navigation Groups
    'navigation' => [
        'data' => 'Data',
        'settings' => 'Settings',
        'events' => 'Events',
    ],

    // Resources - Seminar Registrations
    'seminar_registration' => [
        'label' => 'Seminar Registration',
        'plural_label' => 'Seminar Registrations',
        'form' => [
            'email_plataran' => 'Email as per Plataran Sehat',
            'name_str' => 'Name as per STR (without title)',
            'name_plataran' => 'Name as per Plataran Sehat',
            'nik' => 'NIK',
            'pdgi_branch' => 'PDGI Branch',
            'competency' => 'Competency',
            'whatsapp_number' => 'WhatsApp Number',
        ],
        'table' => [
            'registration_code' => 'Registration Code',
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'selected_seminar' => 'Selected Seminar',
            'payment_status' => 'Payment Status',
            'created_at' => 'Created At',
        ],
    ],

    // Resources - Users
    'user' => [
        'label' => 'User',
        'plural_label' => 'Users',
        'form' => [
            'section_user_info' => 'User Information',
            'name' => 'Name',
            'name_license' => 'Name License',
            'nik' => 'NIK',
            'pdgi_branch' => 'PDGI Branch',
            'kompetensi' => 'Kompetensi',
            'email' => 'Email Address',
            'section_roles' => 'Roles',
            'roles_placeholder' => 'Select roles...',
        ],
        'table' => [
            'name' => 'Name',
            'email' => 'Email',
            'roles' => 'Roles',
            'created_at' => 'Created At',
        ],
    ],

    // Resources - Seminars
    'seminar' => [
        'label' => 'Seminar',
        'plural_label' => 'Seminars',
        'form' => [
            'section_package_info' => 'Package Information',
            'name' => 'Package Name',
            'name_placeholder' => 'e.g., Early Bird - Snack + Lunch',
            'code' => 'Code',
            'code_placeholder' => 'e.g., local_early_bird_lunch',
            'code_helper' => 'Auto-generated from package name on create. Editable on edit page.',
            'description' => 'Description',
            'description_placeholder' => 'Optional description of what this package includes',
            'section_pricing' => 'Pricing & Type',
            'applies_to' => 'Applies To',
            'applies_to_local' => 'Local (Indonesia)',
            'applies_to_international' => 'International',
            'applies_to_all' => 'All Participants',
            'applies_to_helper' => 'Select which participant type this package applies to',
            'original_price' => 'Original Price',
            'original_price_placeholder' => 'e.g., 1000000',
            'original_price_helper' => 'Regular price before any discounts',
            'discounted_price' => 'Discounted Price (Early Bird)',
            'discounted_price_placeholder' => 'e.g., 900000',
            'discounted_price_helper' => 'Early bird promotional price (leave empty for no discount)',
            'max_seats' => 'Max Seats',
            'max_seats_placeholder' => 'e.g., 100',
            'max_seats_helper' => 'Maximum number of registrations allowed (leave empty for unlimited)',
            'early_bird_deadline' => 'Early Bird Deadline',
            'early_bird_deadline_helper' => 'Deadline for early bird pricing (leave empty to use is_early_bird toggle only)',
            'currency' => 'Currency',
            'currency_idr' => 'IDR - Indonesian Rupiah',
            'currency_usd' => 'USD - US Dollar',
            'section_features' => 'Package Features',
            'includes_lunch' => 'Includes Lunch',
            'includes_lunch_helper' => 'Check if this package includes lunch (not just snacks)',
            'is_early_bird' => 'Early Bird Pricing',
            'is_early_bird_helper' => 'Check if this is an early bird promotional price',
            'is_active' => 'Active',
            'is_active_helper' => 'Inactive packages will not be shown to users',
            'section_display_order' => 'Display Order',
            'sort_order' => 'Sort Order',
            'sort_order_helper' => 'Lower numbers appear first. Use this to control display order.',
        ],
        'table' => [
            'name' => 'Name',
            'code' => 'Code',
            'applies_to' => 'Applies To',
            'price' => 'Price',
            'is_active' => 'Active',
            'sort_order' => 'Sort Order',
        ],
    ],

    // Resources - Hands Ons
    'hands_on' => [
        'label' => 'Hands On',
        'plural_label' => 'Hands Ons',
    ],

    // Resources - Hands On Registrations
    'hands_on_registration' => [
        'label' => 'Hands On Registration',
        'plural_label' => 'Hands On Registrations',
    ],

    // Resources - Visitors
    'visitor' => [
        'label' => 'Visitor',
        'plural_label' => 'Visitors',
        'form' => [
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'affiliation' => 'Affiliation',
            'section_attendance' => 'Attendance Status',
            'section_attendance_description' => 'Visitor check-in information',
            'is_scanned' => 'Checked In',
            'scanned_at' => 'Checked In At',
            'not_scanned' => 'Not checked in yet',
        ],
        'table' => [
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'affiliation' => 'Affiliation',
            'checked_in' => 'Checked In',
        ],
    ],

    // Resources - Poster Submissions
    'poster_submission' => [
        'label' => 'Poster Submission',
        'plural_label' => 'Poster Submissions',
    ],

    // Resources - Poster Evaluations
    'poster_evaluation' => [
        'label' => 'Poster Evaluation',
        'plural_label' => 'Poster Evaluations',
    ],

    // Resources - Countries
    'country' => [
        'label' => 'Country',
        'plural_label' => 'Countries',
        'form' => [
            'name' => 'Country Name',
            'code' => 'Country Code',
            'is_indonesia' => 'Indonesia',
        ],
        'table' => [
            'name' => 'Name',
            'code' => 'Code',
            'is_indonesia' => 'Indonesia',
        ],
    ],

    // Resources - Roles
    'role' => [
        'label' => 'Role',
        'plural_label' => 'Roles',
        'form' => [
            'name' => 'Role Name',
            'permissions' => 'Permissions',
        ],
        'table' => [
            'name' => 'Name',
            'permissions_count' => 'Permissions Count',
        ],
    ],

    // Resources - Permissions
    'permission' => [
        'label' => 'Permission',
        'plural_label' => 'Permissions',
        'form' => [
            'name' => 'Permission Name',
        ],
        'table' => [
            'name' => 'Name',
        ],
    ],

    // Common Actions
    'actions' => [
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'view' => 'View',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'back' => 'Back',
        'confirm' => 'Confirm',
    ],

    // Common Fields
    'fields' => [
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'id' => 'ID',
    ],
];
