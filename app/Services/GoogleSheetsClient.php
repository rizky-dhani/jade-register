<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Minimal Google Sheets client for a service account.
 *
 * Deliberately dependency-free: it signs a JWT assertion with ext-openssl and
 * trades it for an access token over the Guzzle that ships with the framework.
 * Authentication is used instead of an API key because writing to a private
 * spreadsheet needs a user, and a service account is the only user that can
 * live on a server.
 */
class GoogleSheetsClient
{
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const SCOPE = 'https://www.googleapis.com/auth/spreadsheets';

    private const BASE_URL = 'https://sheets.googleapis.com/v4/spreadsheets';

    /**
     * @param  array{client_email: string, private_key: string}  $credentials
     */
    public function __construct(
        private readonly array $credentials,
        private readonly string $spreadsheetId,
    ) {}

    public static function fromConfig(): self
    {
        $path = config('services.google_sheets.credentials_path');
        $spreadsheetId = config('services.google_sheets.spreadsheet_id');

        if (! $path || ! is_readable($path)) {
            throw new RuntimeException("Google service-account file is not readable at [{$path}].");
        }

        if (! $spreadsheetId) {
            throw new RuntimeException('GOOGLE_SHEETS_SPREADSHEET_ID is not set.');
        }

        /** @var array{client_email?: string, private_key?: string} $json */
        $json = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        if (empty($json['client_email']) || empty($json['private_key'])) {
            throw new RuntimeException('Google service-account file is missing client_email or private_key.');
        }

        return new self(
            ['client_email' => $json['client_email'], 'private_key' => $json['private_key']],
            $spreadsheetId,
        );
    }

    /**
     * Existing tab titles, keyed by title, with their numeric sheet ids.
     *
     * @return array<string, int>
     */
    public function sheetIds(): array
    {
        $response = $this->request()
            ->get(self::BASE_URL.'/'.$this->spreadsheetId, ['fields' => 'sheets.properties']);

        $response->throw();

        $ids = [];

        foreach ($response->json('sheets') ?? [] as $sheet) {
            $ids[$sheet['properties']['title']] = $sheet['properties']['sheetId'];
        }

        return $ids;
    }

    public function addSheet(string $title): void
    {
        $response = $this->request()->post(self::BASE_URL.'/'.$this->spreadsheetId.':batchUpdate', [
            'requests' => [['addSheet' => ['properties' => ['title' => $title]]]],
        ]);

        $response->throw();
    }

    public function deleteSheet(int $sheetId): void
    {
        $response = $this->request()->post(self::BASE_URL.'/'.$this->spreadsheetId.':batchUpdate', [
            'requests' => [['deleteSheet' => ['sheetId' => $sheetId]]],
        ]);

        $response->throw();
    }

    public function clear(string $title): void
    {
        $response = $this->request()->post(
            $this->valuesUrl($title).':clear',
            ['range' => $this->quoted($title)],
        );

        $response->throw();
    }

    /**
     * Write rows to a tab.
     *
     * RAW is load-bearing: USER_ENTERED would let Sheets parse values like a
     * person typing them, which turns a "+62..." phone number into a formula and
     * truncates a 16-digit NIK past 15 significant digits.
     *
     * @param  array<int, array<int, mixed>>  $rows
     */
    public function write(string $title, array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $response = $this->request()
            ->withQueryParameters(['valueInputOption' => 'RAW'])
            ->put($this->valuesUrl($title), ['values' => $rows]);

        $response->throw();
    }

    private function request(): PendingRequest
    {
        return Http::withToken($this->accessToken())
            ->acceptJson()
            ->asJson()
            ->timeout(60);
    }

    private function valuesUrl(string $title): string
    {
        return self::BASE_URL.'/'.$this->spreadsheetId.'/values/'.rawurlencode($this->quoted($title));
    }

    private function quoted(string $title): string
    {
        return "'".str_replace("'", "''", $title)."'";
    }

    private function accessToken(): string
    {
        // A token lives an hour; refresh before that and keep it out of the
        // per-row code path so a sync makes one auth call, not one per request.
        return Cache::remember('google-sheets-access-token', now()->addMinutes(50), function (): string {
            $response = Http::asForm()->timeout(30)->post(self::TOKEN_URL, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $this->assertion(),
            ]);

            $response->throw();

            $token = $response->json('access_token');

            if (! is_string($token) || $token === '') {
                throw new RuntimeException('Google token endpoint did not return an access token.');
            }

            return $token;
        });
    }

    public function assertion(): string
    {
        $now = time();

        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claims = [
            'iss' => $this->credentials['client_email'],
            'scope' => self::SCOPE,
            'aud' => self::TOKEN_URL,
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        $unsigned = $this->base64Url((string) json_encode($header))
            .'.'.$this->base64Url((string) json_encode($claims));

        if (! openssl_sign($unsigned, $signature, $this->credentials['private_key'], OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Unable to sign the Google service-account assertion.');
        }

        return $unsigned.'.'.$this->base64Url($signature);
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
