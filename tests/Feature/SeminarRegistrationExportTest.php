<?php

use App\Exports\SeminarRegistrationExport;
use App\Filament\Resources\SeminarRegistrations\Pages\ListSeminarRegistrations;
use App\Models\Country;
use App\Models\SeminarRegistration;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

function renderSeminarExport(?Builder $query = null): Spreadsheet
{
    $path = tempnam(sys_get_temp_dir(), 'seminar-export').'.xlsx';
    file_put_contents($path, Excel::raw(new SeminarRegistrationExport(null, $query), ExcelWriter::XLSX));

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

test('international sheet renders a missing status instead of failing', function () {
    // status is nullable in the schema; a row without one must not throw.
    $registration = SeminarRegistration::factory()
        ->for(Country::factory()->international())
        ->create(['status' => null]);

    $rows = seminarSheetRows(renderSeminarExport()->getSheet(1));
    $dataRow = collect($rows)->firstWhere(0, $registration->registration_code);

    expect($dataRow[4])->toBe('-');
});

test('only exports registrations matching the filtered query', function () {
    $country = Country::factory()->indonesia()->create();

    $inside = SeminarRegistration::factory()->for($country)->create([
        'registration_code' => 'JADE-SEM-2026-900001',
        'created_at' => '2026-11-10 09:00:00',
    ]);

    $outside = SeminarRegistration::factory()->for($country)->create([
        'registration_code' => 'JADE-SEM-2026-900002',
        'created_at' => '2026-10-01 09:00:00',
    ]);

    // What the attendance desk hands over when a creation-date filter is applied.
    $query = SeminarRegistration::query()->whereDate('created_at', '>=', '2026-11-05');

    $codes = array_column(seminarSheetRows(renderSeminarExport($query)->getSheet(0)), 0);

    expect($codes)->toContain($inside->registration_code)
        ->not->toContain($outside->registration_code);
});

test('the list page passes its filtered query to the export', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');
    actingAs($admin);

    $country = Country::factory()->indonesia()->create();

    $kept = SeminarRegistration::factory()->for($country)->create([
        'registration_code' => 'JADE-SEM-2026-900003',
        'created_at' => '2026-11-10 09:00:00',
    ]);
    $dropped = SeminarRegistration::factory()->for($country)->create([
        'registration_code' => 'JADE-SEM-2026-900004',
        'created_at' => '2026-10-01 09:00:00',
    ]);

    $component = livewire(ListSeminarRegistrations::class)
        ->filterTable('created_at', ['created_from' => '2026-11-05']);

    $query = $component->instance()->getTableQueryForExport();

    expect($query->pluck('registration_code')->all())
        ->toContain($kept->registration_code)
        ->not->toContain($dropped->registration_code);

    // And that same query drives the workbook.
    $codes = array_column(seminarSheetRows(renderSeminarExport($query)->getSheet(0)), 0);
    expect($codes)->toContain($kept->registration_code)
        ->not->toContain($dropped->registration_code);
});

test('the export action on the list page returns a download', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');
    actingAs($admin);

    SeminarRegistration::factory()->for(Country::factory()->indonesia()->create())->create();

    livewire(ListSeminarRegistrations::class)
        ->callAction('export')
        ->assertFileDownloaded();
});

test('the export ignores the active tab so both segments are included', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');
    actingAs($admin);

    $local = SeminarRegistration::factory()
        ->for(Country::factory()->indonesia()->create())
        ->create(['registration_code' => 'JADE-SEM-2026-900005']);
    $international = SeminarRegistration::factory()
        ->for(Country::factory()->international()->create())
        ->create(['registration_code' => 'JADE-SEM-2026-900006']);

    // The desk is looking at the "Local" tab; the export must not inherit it.
    $component = livewire(ListSeminarRegistrations::class)->set('activeTab', 'local');

    $query = $component->instance()->getTableQueryForExportIgnoringTabs();

    expect($query->pluck('registration_code')->all())
        ->toContain($local->registration_code)
        ->toContain($international->registration_code);

    $workbook = renderSeminarExport($query);
    expect(array_column(seminarSheetRows($workbook->getSheet(0)), 0))->toContain($local->registration_code);
    expect(array_column(seminarSheetRows($workbook->getSheet(1)), 0))->toContain($international->registration_code);
});
