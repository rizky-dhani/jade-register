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

test('summary sheet is first and lists joined hands on codes per participant', function () {
    $seminar = SeminarRegistration::factory()->create([
        'name_license' => 'Multi Session Dentist',
        'email' => 'multi@example.com',
    ]);

    $first = HandsOn::factory()->create(['ho_code' => 'HO-01', 'event_date' => '2026-11-13']);
    $second = HandsOn::factory()->create(['ho_code' => 'HO-05', 'event_date' => '2026-11-14']);
    $solo = HandsOn::factory()->create(['ho_code' => 'HO-09', 'event_date' => '2026-11-15']);

    HandsOnRegistration::factory()->for($first)->for($seminar)->create(['created_at' => now()->subDay()]);
    HandsOnRegistration::factory()->for($second)->for($seminar)->create(['created_at' => now()]);
    HandsOnRegistration::factory()->for($solo)->for(
        SeminarRegistration::factory()->create(['name_license' => 'Solo Dentist', 'email' => 'solo@example.com'])
    )->create(['created_at' => now()->subDays(2)]);

    $workbook = renderHandsOnExport();

    expect($workbook->getSheetNames()[0])->toBe('Summary');

    $rows = sheetRows($workbook->getSheet(0));
    expect($rows[0])->toBe([
        'HO Registration Code',
        'Seminar Registration',
        'Seminar Reg Code',
        'Joined Hands On',
        'Participant Name',
        'Email',
        'Register Type',
        'Payment Status',
        'Created At',
        'Verified At',
    ]);

    $dataRows = collect($rows)->slice(1);
    expect($dataRows->firstWhere(5, 'multi@example.com')[3])->toBe('HO-01, HO-05');
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
        'HO Registration Code',
        'Seminar Registration',
        'Seminar Reg Code',
        'Participant Name',
        'Email',
        'Register Type',
        'Payment Status',
        'Created At',
        'Verified At',
    ]);
});
