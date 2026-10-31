<?php

use App\Models\Country;
use App\Services\PhoneNumber;

function countryWithDialingCode(string $code, bool $isIndonesia = false): Country
{
    return new Country([
        'name' => 'Test',
        'code' => 'TC',
        'is_indonesia' => $isIndonesia,
        'phone_code' => $code,
    ]);
}

function indonesia(): Country
{
    return countryWithDialingCode('+62', true);
}

it('prefixes a local trunk-0 number with the dialing code', function () {
    expect(PhoneNumber::normalize('081234567890', indonesia()))->toBe('+6281234567890');
});

it('leaves an already normalised number untouched', function () {
    expect(PhoneNumber::normalize('+6281234567890', indonesia()))->toBe('+6281234567890');
});

it('adds the plus when the country code was typed without one', function () {
    expect(PhoneNumber::normalize('6281234567890', indonesia()))->toBe('+6281234567890');
});

it('accepts a bare mobile number without a trunk prefix', function () {
    expect(PhoneNumber::normalize('81234567890', indonesia()))->toBe('+6281234567890');
});

it('strips separators and spaces before normalising', function () {
    expect(PhoneNumber::normalize('0812-3456-7890', indonesia()))->toBe('+6281234567890')
        ->and(PhoneNumber::normalize('0812 3456 7890', indonesia()))->toBe('+6281234567890')
        ->and(PhoneNumber::normalize('(0812) 3456 7890', indonesia()))->toBe('+6281234567890');
});

it('drops the landline trunk zero too', function () {
    expect(PhoneNumber::normalize('0211234567', indonesia()))->toBe('+62211234567');
});

it('trusts an explicit plus even when it disagrees with the country', function () {
    expect(PhoneNumber::normalize('+6591234567', indonesia()))->toBe('+6591234567');
});

it('uses the participant country dialing code for international numbers', function () {
    $singapore = countryWithDialingCode('+65');

    expect(PhoneNumber::normalize('91234567', $singapore))->toBe('+6591234567')
        ->and(PhoneNumber::normalize('0912345678', $singapore))->toBe('+65912345678')
        ->and(PhoneNumber::normalize('6591234567', $singapore))->toBe('+6591234567');
});

it('falls back to the indonesian dialing code when no country is known', function () {
    expect(PhoneNumber::normalize('081234567890'))->toBe('+6281234567890')
        ->and(PhoneNumber::normalize('081234567890', new Country(['phone_code' => null])))->toBe('+6281234567890');
});

it('requires a plausible subscriber length before assuming a country code', function () {
    // A short "+1" country number must not be mistaken for an already coded one.
    $unitedStates = countryWithDialingCode('+1');

    expect(PhoneNumber::normalize('1234', $unitedStates))->toBe('+11234')
        ->and(PhoneNumber::normalize('4155552671', $unitedStates))->toBe('+14155552671');
});

it('returns null for numbers that carry no digits', function () {
    expect(PhoneNumber::normalize(null))->toBeNull()
        ->and(PhoneNumber::normalize(''))->toBeNull()
        ->and(PhoneNumber::normalize('   '))->toBeNull()
        ->and(PhoneNumber::normalize('-'))->toBeNull();
});
