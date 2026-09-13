<?php

use App\Exports\HandsOnRegistrationExport;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

uses(RefreshDatabase::class);

function renderHandsOnExport(): Spreadsheet
{
    $path = tempnam(sys_get_temp_dir(), 'ho-export').'.xlsx';
    file_put_contents($path, Excel::raw(new HandsOnRegistrationExport, ExcelWriter::XLSX));

    $workbook = IOFactory::load($path);
    unlink($path);

    return $workbook;
}

function sheetRows(Worksheet $sheet): array
{
    $rows = [];
    for ($row = 1; $row <= $sheet->getHighestRow(); $row++) {
        $rows[] = array_map(
            fn (string $column) => $sheet->getCell($column.$row)->getValue(),
            range('A', $sheet->getHighestColumn()),
        );
    }

    return $rows;
}

/**
 * Split the summary sheet into its totals table and participant table, each with its header row first.
 *
 * @return array{totals: array<int, array>, participants: array<int, array>}
 */
function summarySections(Worksheet $sheet): array
{
    $rows = collect(sheetRows($sheet))
        ->reject(fn (array $row) => collect($row)->filter()->isEmpty())
        ->values();

    $participantHeader = $rows->search(fn (array $row) => $row[0] === 'HO Reg Code');

    return [
        'totals' => $rows->take($participantHeader)->values()->all(),
        'participants' => $rows->slice($participantHeader)->values()->all(),
    ];
}

test('summary sheet is first and lists joined hands on codes per participant', function () {
    $seminar = SeminarRegistration::factory()->create([
        'name_license' => 'Multi Session Dentist',
        'email' => 'multi@example.com',
    ]);

    $first = HandsOn::factory()->create(['ho_code' => 'HO-01', 'event_date' => '2026-11-13']);
    $second = HandsOn::factory()->create(['ho_code' => 'HO-05', 'event_date' => '2026-11-14']);
    $solo = HandsOn::factory()->create(['ho_code' => 'HO-09', 'event_date' => '2026-11-15']);

    $multiFirst = HandsOnRegistration::factory()->for($first)->for($seminar)->create([
        'registration_code' => 'JADE-HO-2026-000101',
        'created_at' => now()->subDay(),
    ]);
    $multiLatest = HandsOnRegistration::factory()->for($second)->for($seminar)->create([
        'registration_code' => 'JADE-HO-2026-000102',
        'created_at' => now(),
    ]);
    HandsOnRegistration::factory()->for($solo)->for(
        SeminarRegistration::factory()->create(['name_license' => 'Solo Dentist', 'email' => 'solo@example.com'])
    )->create(['created_at' => now()->subDays(2)]);

    $workbook = renderHandsOnExport();

    expect($workbook->getSheetNames()[0])->toBe('Summary');

    $sections = summarySections($workbook->getSheet(0));

    expect($sections['participants'][0])->toBe([
        'HO Reg Code',
        'Join Seminar?',
        'Seminar Reg Code',
        'Joined Hands On',
        'Participant Name',
        'Email',
        'Register Type',
        'Payment Status',
        'Created At',
        'Verified At',
    ]);

    $dataRows = collect($sections['participants'])->slice(1);

    $multiRows = $dataRows->where(5, 'multi@example.com')->values();
    expect($multiRows)->toHaveCount(1);
    expect($multiRows[0][3])->toBe('HO-01, HO-05');
    expect($multiRows[0][0])->toBe($multiLatest->registration_code);
    expect($dataRows->pluck(0))->not->toContain($multiFirst->registration_code);

    expect($dataRows->firstWhere(5, 'solo@example.com')[3])->toBe('HO-09');

    $createdAt = $dataRows->pluck(8)->all();
    expect($createdAt)->toBe(collect($createdAt)->sort()->values()->all());
});

test('session sheets keep the original columns without joined hands on', function () {
    $handsOn = HandsOn::factory()->create(['ho_code' => 'HO-02', 'event_date' => '2026-11-13']);
    HandsOnRegistration::factory()->for($handsOn)->create();

    $workbook = renderHandsOnExport();

    expect($workbook->getSheetNames()[1])->toStartWith('HO-02 - ');

    $headings = sheetRows($workbook->getSheet(1))[0];
    expect($headings)->toBe([
        'HO Reg Code',
        'Join Seminar?',
        'Seminar Reg Code',
        'Participant Name',
        'Email',
        'Register Type',
        'Payment Status',
        'Created At',
        'Verified At',
    ]);
});

test('summary sheet totals table counts paid and pending per hands on', function () {
    $handsOn = HandsOn::factory()->create(['ho_code' => 'HO-03', 'event_date' => '2026-11-14']);

    HandsOnRegistration::factory()->count(2)->for($handsOn)->verified()->create();
    HandsOnRegistration::factory()->for($handsOn)->create(['payment_status' => 'pending']);
    HandsOnRegistration::factory()->for($handsOn)->create(['payment_status' => 'rejected']);

    $totals = collect(summarySections(renderHandsOnExport()->getSheet(0))['totals'])
        ->map(fn (array $row) => array_slice($row, 0, 4));

    expect($totals->first())->toBe(['HO Code', 'Paid', 'Pending', 'Total']);
    expect($totals->firstWhere(0, 'HO-03'))->toBe(['HO-03', 2, 1, 3]);
});
