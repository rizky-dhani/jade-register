<?php

namespace App\Services;

use App\Models\Country;

/**
 * Turns the free-form phone numbers captured by the registration forms into
 * E.164, which is what address books and dialers expect. Normalization happens
 * on read so historical rows benefit without a data migration.
 */
class PhoneNumber
{
    /**
     * Country assumed when a participant has no country on record, matching how
     * the rest of the app treats a missing country as local.
     */
    public const DEFAULT_DIALING_CODE = '62';

    public static function normalize(?string $phone, ?Country $country = null): ?string
    {
        $raw = trim((string) $phone);

        if ($raw === '') {
            return null;
        }

        $hasPlus = str_starts_with($raw, '+');
        $digits = preg_replace('/\D+/', '', $raw) ?? '';

        if ($digits === '') {
            return null;
        }

        // An explicit + means the number already carries its own country code.
        if ($hasPlus) {
            return '+'.$digits;
        }

        $code = static::dialingCode($country);

        if (static::alreadyHasDialingCode($digits, $code)) {
            return '+'.$digits;
        }

        // Indonesian numbers always carry a 0 trunk prefix; most other countries do too.
        $national = str_starts_with($digits, '0') ? substr($digits, 1) : $digits;

        return '+'.$code.$national;
    }

    public static function dialingCode(?Country $country = null): string
    {
        $code = preg_replace('/\D+/', '', (string) $country?->phone_code) ?? '';

        return $code === '' ? self::DEFAULT_DIALING_CODE : $code;
    }

    /**
     * Guard against mistaking a national number for a country-coded one, e.g.
     * "1234567890" in a "+1" country, by requiring a plausible subscriber length.
     */
    protected static function alreadyHasDialingCode(string $digits, string $code): bool
    {
        return str_starts_with($digits, $code)
            && mb_strlen($digits) >= mb_strlen($code) + 5;
    }
}
