<?php

use App\Exports\SeminarRegistrationExport;
use App\Models\Country;
use App\Models\SeminarRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

uses(RefreshDatabase::class);

function renderSeminarExport(): Spreadsheet
{
    $path = tempnam(sys_get_temp_dir(), 'seminar-export').'.xlsx';
    file_put_contents($path, Excel::raw(new SeminarRegistrationExport, ExcelWriter::XLSX));

    $workbook = IOFactory::load($path);
    unlink($path);

    return $workbook;
}

function seminarSheetRows(Worksheet $sheet): array
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
 * Non-empty values in one column, each paired with the type PhpSpreadsheet stored it as.
 *
 * @return array<int, array{0: string, 1: string}>
 */
function seminarColumnValues(Worksheet $sheet, string $column): array
{
    $values = [];

    for ($row = 1; $row <= $sheet->getHighestRow(); $row++) {
        $cell = $sheet->getCell($column.$row);

        if ($cell->getValue() === null || $cell->getValue() === '') {
            continue;
        }

        $values[] = [$cell->getValue(), $cell->getDataType()];
    }

    return $values;
}

test('sheets are named Local and International', function () {
    expect(renderSeminarExport()->getSheetNames())->toBe(['Local', 'International']);
});

test('local sheet lists tanggal daftar as the second to last column', function () {
    $registration = SeminarRegistration::factory()
        ->for(Country::factory()->indonesia())
        ->create([
            'name_license' => 'Local Dentist',
            'created_at' => '2026-11-13 09:30:00',
            'phone' => '081234567890',
        ]);

    $rows = seminarSheetRows(renderSeminarExport()->getSheet(0));

    expect($rows[0])->toBe([
        'Kode Peserta',
        'Nama sesuai Plataran Sehat',
        'Email',
        'No Telp',
        'NIK',
        'Cabang PDGI',
        'Seminar',
        'Harga Paket Seminar',
        'Status Pembayaran',
        'Kompetensi',
        'Metode Pembayaran',
        'Tanggal Daftar',
        'Ingin Ikut Hands On?',
    ]);

    $dataRow = collect($rows)->firstWhere(0, $registration->registration_code);
    expect($dataRow[11])->toBe('2026-11-13 09:30:00');
    expect($dataRow[3])->toBe('081234567890');
});

test('international sheet lists tanggal daftar as the second to last column', function () {
    $registration = SeminarRegistration::factory()
        ->for(Country::factory()->international())
        ->create([
            'status' => 'Dentist',
            'created_at' => '2026-11-14 17:45:00',
            'phone' => '+6281299990000',
        ]);

    $rows = seminarSheetRows(renderSeminarExport()->getSheet(1));

    expect($rows[0])->toBe([
        'Kode Peserta',
        'Nama',
        'Email',
        'No Telp',
        'Status',
        'Negara',
        'Seminar',
        'Metode Pembayaran',
        'Harga Paket Seminar',
        'Tanggal Daftar',
        'Ingin Ikut Hands On?',
    ]);

    $dataRow = collect($rows)->firstWhere(0, $registration->registration_code);
    expect($dataRow[9])->toBe('2026-11-14 17:45:00');

    // A +62 number must survive as text, or Excel shows it as 6.28129E+12.
    expect(seminarColumnValues(renderSeminarExport()->getSheet(1), 'D'))
        ->toBe([['No Telp', 's'], ['+6281299990000', 's']]);
});

test('local sheet keeps phone and NIK as text', function () {
    SeminarRegistration::factory()
        ->for(Country::factory()->indonesia())
        ->create(['phone' => '+6281200000001', 'nik' => '3171234567890001']);

    $workbook = renderSeminarExport();

    expect(seminarColumnValues($workbook->getSheet(0), 'D'))
        ->toBe([['No Telp', 's'], ['+6281200000001', 's']]);
    expect(seminarColumnValues($workbook->getSheet(0), 'E'))
        ->toBe([['NIK', 's'], ['3171234567890001', 's']]);
});
