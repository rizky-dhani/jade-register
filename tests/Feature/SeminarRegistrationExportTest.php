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

test('local sheet lists tanggal daftar as the second to last column', function () {
    $registration = SeminarRegistration::factory()
        ->for(Country::factory()->indonesia())
        ->create([
            'name_license' => 'Local Dentist',
            'created_at' => '2026-11-13 09:30:00',
        ]);

    $rows = seminarSheetRows(renderSeminarExport()->getSheet(0));

    expect($rows[0])->toBe([
        'Kode Peserta',
        'Nama sesuai Plataran Sehat',
        'Email',
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
    expect($dataRow[10])->toBe('2026-11-13 09:30:00');
});

test('international sheet lists tanggal daftar as the second to last column', function () {
    $registration = SeminarRegistration::factory()
        ->for(Country::factory()->international())
        ->create([
            'name' => 'Foreign Dentist',
            'status' => 'Dentist',
            'created_at' => '2026-11-14 17:45:00',
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
});
