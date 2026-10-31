<?php

use App\Services\GoogleSheetsClient;

/**
 * A throwaway RSA key so signing is exercised for real.
 *
 * @return array{0: string, 1: string} private key, public key
 */
function sheetsTestKeypair(): array
{
    $resource = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    openssl_pkey_export($resource, $privateKey);
    $publicKey = openssl_pkey_get_details($resource)['key'];

    return [$privateKey, $publicKey];
}

/**
 * @param  array{0: string, 1: string}  $keypair
 */
function sheetsTestClient(array $keypair, string $email = 'sync@example.iam.gserviceaccount.com'): GoogleSheetsClient
{
    return new GoogleSheetsClient(
        ['client_email' => $email, 'private_key' => $keypair[0]],
        'spreadsheet-123',
    );
}

/**
 * @return array{0: array<string, mixed>, 1: array<string, mixed>}
 */
function decodeJwtParts(string $jwt): array
{
    $parts = explode('.', $jwt);

    expect($parts)->toHaveCount(3);

    $decode = fn (string $segment) => json_decode(
        base64_decode(strtr($segment, '-_', '+/')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    return [$decode($parts[0]), $decode($parts[1])];
}

test('the assertion is a three part jwt advertising RS256', function () {
    $client = sheetsTestClient(sheetsTestKeypair());

    $parts = explode('.', $client->assertion());

    expect($parts)->toHaveCount(3);

    [$header, $claims] = decodeJwtParts($client->assertion());

    expect($header)->toBe(['alg' => 'RS256', 'typ' => 'JWT']);
    expect($claims['iss'])->toBe('sync@example.iam.gserviceaccount.com');
    expect($claims['scope'])->toBe('https://www.googleapis.com/auth/spreadsheets');
    expect($claims['aud'])->toBe('https://oauth2.googleapis.com/token');
    expect($claims['exp'] - $claims['iat'])->toBe(3600);
});

test('the assertion signature verifies against the public key', function () {
    $keypair = sheetsTestKeypair();
    $client = sheetsTestClient($keypair);

    $assertion = $client->assertion();
    $parts = explode('.', $assertion);

    $verified = openssl_verify(
        $parts[0].'.'.$parts[1],
        base64_decode(strtr($parts[2], '-_', '+/')),
        $keypair[1],
        OPENSSL_ALGO_SHA256,
    );

    expect($verified)->toBe(1);
});

test('the assertion is base64url encoded without padding', function () {
    $assertion = sheetsTestClient(sheetsTestKeypair())->assertion();

    expect($assertion)->not->toContain('+')
        ->and($assertion)->not->toContain('/')
        ->and($assertion)->not->toContain('=');
});
