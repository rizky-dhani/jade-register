<?php

use App\Exports\InternationalParticipantsSheet;
use App\Exports\LocalParticipantsSheet;
use App\Models\Country;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use App\Services\RegistrationSheetMirror;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @return array<int, array{title: string, rows: array<int, array<int, mixed>>}>
 */
function mirroredTables(): array
{
    return app(RegistrationSheetMirror::class)->tables();
}

/**
 * @param  array<int, array{title: string, rows: array<int, array<int, mixed>>}>  $tables
 * @return array<int, array<int, mixed>>
 */
function mirroredTable(array $tables, string $title): array
{
    $table = collect($tables)->firstWhere('title', $title);

    expect($table)->not->toBeNull("Tab [{$title}] was not mirrored.");

    return $table['rows'];
}

test('it mirrors both exports with prefixed tab names', function () {
    SeminarRegistration::factory()->for(Country::factory()->indonesia())->create();
    SeminarRegistration::factory()->for(Country::factory()->international())->create();

    $handsOn = HandsOn::factory()->create(['ho_code' => 'HO-07', 'event_date' => '2026-11-13', 'name' => 'Layering']);
    HandsOnRegistration::factory()->for($handsOn)->create();

    $titles = collect(mirroredTables())->pluck('title')->all();

    expect($titles)->toContain('Seminar Local')
        ->toContain('Seminar International')
        ->toContain('HandsOn Summary');

    expect(collect($titles)->first(fn (string $t) => str_starts_with($t, 'HandsOn HO-07')))
        ->not->toBeNull();
});

test('tab headings match the export headings exactly', function () {
    SeminarRegistration::factory()->for(Country::factory()->indonesia())->create();

    $tables = mirroredTables();

    $localHeadings = mirroredTable($tables, 'Seminar Local')[0];
    $internationalHeadings = mirroredTable($tables, 'Seminar International')[0];

    expect($localHeadings)->toBe((new LocalParticipantsSheet)->headings())
        ->and($internationalHeadings)->toBe((new InternationalParticipantsSheet)->headings());
});

test('phone numbers and nik stay strings so the sheet cannot show scientific notation', function () {
    SeminarRegistration::factory()
        ->for(Country::factory()->indonesia())
        ->create(['phone' => '+6281200000001', 'nik' => '3171234567890001']);

    $rows = mirroredTable(mirroredTables(), 'Seminar Local');
    $data = $rows[1];

    expect($data[3])->toBe('+6281200000001')
        ->and($data[4])->toBe('3171234567890001');
});

test('the hands on summary keeps its totals block and joined sessions column', function () {
    $seminar = SeminarRegistration::factory()->create(['name_license' => 'Multi Dentist', 'email' => 'multi@example.com']);
    $first = HandsOn::factory()->create(['ho_code' => 'HO-01', 'event_date' => '2026-11-13', 'name' => 'First']);
    $second = HandsOn::factory()->create(['ho_code' => 'HO-05', 'event_date' => '2026-11-14', 'name' => 'Second']);

    HandsOnRegistration::factory()->for($first)->for($seminar)->verified()->create();
    HandsOnRegistration::factory()->for($second)->for($seminar)->verified()->create();

    $rows = mirroredTable(mirroredTables(), 'HandsOn Summary');

    // Totals table is injected above the participant list by the export.
    $totalsHeader = collect($rows)->first(fn (array $row) => ($row[0] ?? null) === 'HO Code');
    expect($totalsHeader[0])->toBe('HO Code');

    $participantHeader = collect($rows)->first(fn (array $row) => ($row[0] ?? null) === 'HO Reg Code');
    expect($participantHeader)->toContain('Joined Hands On')->toContain('No Telp');
});

test('every mirrored row is json serialisable for the sheets api', function () {
    SeminarRegistration::factory()->for(Country::factory()->indonesia())->create();

    foreach (mirroredTables() as $table) {
        expect(json_encode($table['rows']))->not->toBeFalse();
    }
});
