<?php

$keys = [
    'digital_workshop_title',
    'digital_workshop_subtitle',
    'digital_workshop_expect_heading',
    'digital_workshop_benefit_followers',
    'digital_workshop_benefit_algorithm',
    'digital_workshop_benefit_depth',
    'digital_workshop_benefit_followup',
    'digital_workshop_closing',
    'digital_workshop_price_standalone',
    'digital_workshop_price_bundle',
    'digital_workshop_cta',
    'digital_workshop_opens_soon',
];

test('defines every digital workshop landing key in indonesian', function () use ($keys) {
    foreach ($keys as $key) {
        $value = trans('seminar.'.$key, [], 'id');

        expect($value)
            ->toBeString()
            ->not->toBe('seminar.'.$key)
            ->and(trim($value))->not->toBe('');
    }
});

test('defines every digital workshop landing key in english', function () use ($keys) {
    foreach ($keys as $key) {
        $value = trans('seminar.'.$key, [], 'en');

        expect($value)
            ->toBeString()
            ->not->toBe('seminar.'.$key)
            ->and(trim($value))->not->toBe('');
    }
});

test('does not leave english copy in the indonesian file', function () use ($keys) {
    // A copy-paste that never got translated returns the identical string.
    // digital_workshop_title is intentionally excluded: it is "Digital Workshop"
    // in both languages.
    $translatable = array_values(array_diff($keys, ['digital_workshop_title']));

    foreach ($translatable as $key) {
        expect(trans('seminar.'.$key, [], 'id'))->not->toBe(trans('seminar.'.$key, [], 'en'));
    }
});
