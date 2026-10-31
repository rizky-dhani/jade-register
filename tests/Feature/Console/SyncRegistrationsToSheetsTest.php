<?php

use App\Models\Country;
use App\Models\SeminarRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();

    $resource = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    openssl_pkey_export($resource, $privateKey);

    $path = tempnam(sys_get_temp_dir(), 'sa').'.json';
    file_put_contents($path, json_encode([
        'client_email' => 'sync@example.iam.gserviceaccount.com',
        'private_key' => $privateKey,
    ]));

    config([
        'services.google_sheets.enabled' => true,
        'services.google_sheets.spreadsheet_id' => 'spreadsheet-abc',
        'services.google_sheets.credentials_path' => $path,
    ]);
});

/**
 * Fake every endpoint the sync touches, and record the requests made.
 */
function fakeSheetsApi(array $existingTitles = ['Sheet1']): void
{
    Http::fake(function ($request) use ($existingTitles) {
        $url = $request->url();

        if (str_contains($url, 'oauth2.googleapis.com/token')) {
            return Http::response(['access_token' => 'fake-token', 'expires_in' => 3600]);
        }

        if ($request->method() === 'GET' && str_contains($url, 'spreadsheets/spreadsheet-abc')) {
            $sheets = [];

            foreach ($existingTitles as $index => $title) {
                $sheets[] = ['properties' => ['title' => $title, 'sheetId' => $index + 100]];
            }

            return Http::response(['sheets' => $sheets]);
        }

        return Http::response([]);
    });
}

test('does nothing when the sync is disabled', function () {
    config(['services.google_sheets.enabled' => false]);
    Http::fake();

    $this->artisan('sheets:sync-registrations')->assertSuccessful();

    Http::assertNothingSent();
});

test('writes registrations with RAW input so phone numbers are not parsed', function () {
    SeminarRegistration::factory()
        ->for(Country::factory()->indonesia())
        ->create(['phone' => '+6281200000001']);
    SeminarRegistration::factory()->for(Country::factory()->international())->create();

    fakeSheetsApi();

    $this->artisan('sheets:sync-registrations')->assertSuccessful();

    // RAW is what stops Sheets turning "+62..." into a formula and truncating NIK.
    Http::assertSent(function ($request) {
        return $request->method() === 'PUT'
            && str_contains($request->url(), 'valueInputOption=RAW')
            && str_contains(urldecode($request->url()), 'Seminar Local');
    });

    $written = collect(Http::recorded())
        ->map(fn ($pair) => $pair[0])
        ->first(fn ($request) => $request->method() === 'PUT' && str_contains(urldecode($request->url()), 'Seminar Local'));

    expect($written)->not->toBeNull();
    expect($written->data()['values'][1][3])->toBe('+6281200000001');
});

test('creates a missing tab before writing it', function () {
    SeminarRegistration::factory()->for(Country::factory()->indonesia())->create();

    fakeSheetsApi(['Sheet1']);

    $this->artisan('sheets:sync-registrations')->assertSuccessful();

    $batched = collect(Http::recorded())
        ->map(fn ($pair) => $pair[0])
        ->filter(fn ($request) => str_contains($request->url(), ':batchUpdate'))
        ->flatMap(fn ($request) => $request->data()['requests'] ?? []);

    $added = $batched->pluck('addSheet.properties.title')->filter()->all();

    expect($added)->toContain('Seminar Local')->toContain('HandsOn Summary');
});

test('deletes tabs that no longer correspond to an export', function () {
    SeminarRegistration::factory()->for(Country::factory()->indonesia())->create();

    fakeSheetsApi(['Sheet1', 'Stale Tab']);

    $this->artisan('sheets:sync-registrations')->assertSuccessful();

    $deleted = collect(Http::recorded())
        ->map(fn ($pair) => $pair[0])
        ->filter(fn ($request) => str_contains($request->url(), ':batchUpdate'))
        ->flatMap(fn ($request) => $request->data()['requests'] ?? [])
        ->pluck('deleteSheet.sheetId')
        ->filter()
        ->values()
        ->all();

    // Sheet1 => 100, Stale Tab => 101
    expect($deleted)->toBe([100, 101]);
});

test('clears each tab before rewriting it', function () {
    SeminarRegistration::factory()->for(Country::factory()->indonesia())->create();

    fakeSheetsApi();

    $this->artisan('sheets:sync-registrations')->assertSuccessful();

    Http::assertSent(fn ($request) => $request->method() === 'POST'
        && str_contains($request->url(), ':clear'));
});

test('dry run reports tabs without calling google', function () {
    SeminarRegistration::factory()->for(Country::factory()->indonesia())->create();

    Http::fake();

    $this->artisan('sheets:sync-registrations', ['--dry-run' => true])
        ->expectsOutputToContain('Seminar Local')
        ->assertSuccessful();

    Http::assertNothingSent();
});

test('reports failure instead of throwing when credentials are missing', function () {
    config(['services.google_sheets.credentials_path' => '/nonexistent/sa.json']);
    Http::fake();

    $this->artisan('sheets:sync-registrations')->assertFailed();
});
