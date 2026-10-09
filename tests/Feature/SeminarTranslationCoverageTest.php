<?php

/**
 * Every literal `seminar.*` key referenced in a view or component must resolve in
 * both locales. A missing key renders as the raw key string on a live page, which
 * is how `seminar.phone` and `seminar.submitting` shipped unnoticed.
 */
function referencedSeminarKeys(): array
{
    $sources = array_merge(
        glob(resource_path('views/**/*.blade.php')) ?: [],
        glob(resource_path('views/*.blade.php')) ?: [],
        glob(app_path('**/*.php')) ?: [],
    );

    $keys = [];

    foreach ($sources as $file) {
        $contents = file_get_contents($file);

        // Only literal keys: `__('seminar.foo')`, `trans('seminar.foo', ...)`.
        // Dynamic concatenation (`__('seminar.'.$x)`) is resolved from data, not here.
        preg_match_all(
            "/(?:__|trans)\(\s*'seminar\.([a-z0-9_]+)'/i",
            $contents,
            $matches
        );

        foreach ($matches[1] as $key) {
            $keys[$key] = true;
        }
    }

    return array_keys($keys);
}

test('every referenced seminar key resolves in indonesian', function () {
    $missing = [];

    foreach (referencedSeminarKeys() as $key) {
        $value = trans('seminar.'.$key, [], 'id');

        if ($value === 'seminar.'.$key || trim((string) $value) === '') {
            $missing[] = $key;
        }
    }

    expect($missing)->toBe([], 'Missing in lang/id/seminar.php: '.implode(', ', $missing));
});

test('every referenced seminar key resolves in english', function () {
    $missing = [];

    foreach (referencedSeminarKeys() as $key) {
        $value = trans('seminar.'.$key, [], 'en');

        if ($value === 'seminar.'.$key || trim((string) $value) === '') {
            $missing[] = $key;
        }
    }

    expect($missing)->toBe([], 'Missing in lang/en/seminar.php: '.implode(', ', $missing));
});

test('keeps the indonesian and english seminar files in key parity', function () {
    $id = require lang_path('id/seminar.php');
    $en = require lang_path('en/seminar.php');

    expect(array_keys(array_diff_key($id, $en)))->toBe([], 'Only in lang/id/seminar.php')
        ->and(array_keys(array_diff_key($en, $id)))->toBe([], 'Only in lang/en/seminar.php');
});
