<?php

use App\Exports\HandsOnRegistrationExport;
use App\Filament\Resources\HandsOnRegistrations\Pages\ListHandsOnRegistrations;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
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

function renderHandsOnExport(?Builder $query = null): Spreadsheet
{
    $path = tempnam(sys_get_temp_dir(), 'ho-export').'.xlsx';
    file_put_contents($path, Excel::raw(new HandsOnRegistrationExport($query), ExcelWriter::XLSX));

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
 * Non-empty values in one column, each paired with the type PhpSpreadsheet stored it as.
 *
 * @return array<int, array{0: string, 1: string}>
 */
function columnValues(Worksheet $sheet, string $column): array
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
        'phone' => '+6281200000001',
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
        'No Telp',
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
    expect($multiRows[0][6])->toBe('+6281200000001');
    expect($dataRows->pluck(0))->not->toContain($multiFirst->registration_code);

    expect($dataRows->firstWhere(5, 'solo@example.com')[3])->toBe('HO-09');

    $createdAt = $dataRows->pluck(9)->all();
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
        'No Telp',
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

test('phone numbers are stored as text so excel does not show scientific notation', function () {
    $handsOn = HandsOn::factory()->create(['ho_code' => 'HO-04', 'event_date' => '2026-11-13']);
    $seminar = SeminarRegistration::factory()->create(['phone' => '+6281200000001']);
    HandsOnRegistration::factory()->for($handsOn)->for($seminar)->create();

    $workbook = renderHandsOnExport();

    // Summary splices Joined Hands On in front of the phone column, so it lands in G; session sheets keep it in F.
    expect(columnValues($workbook->getSheet(0), 'G'))->toBe([['No Telp', 's'], ['+6281200000001', 's']]);
    expect(columnValues($workbook->getSheet(1), 'F'))->toBe([['No Telp', 's'], ['+6281200000001', 's']]);
});

test('only exports registrations matching the filtered query', function () {
    $kept = HandsOn::factory()->create(['ho_code' => 'HO-10', 'event_date' => '2026-11-13']);
    $dropped = HandsOn::factory()->create(['ho_code' => 'HO-11', 'event_date' => '2026-11-14']);

    HandsOnRegistration::factory()->for($kept)->verified()->create([
        'registration_code' => 'JADE-HO-2026-900001',
        'created_at' => '2026-11-10 09:00:00',
    ]);
    HandsOnRegistration::factory()->for($dropped)->verified()->create([
        'registration_code' => 'JADE-HO-2026-900002',
        'created_at' => '2026-10-01 09:00:00',
    ]);

    // What the attendance desk hands over when a creation-date filter is applied.
    $workbook = renderHandsOnExport(
        HandsOnRegistration::query()->whereDate('created_at', '>=', '2026-11-05'),
    );

    // Only the session left with a matching row gets a sheet.
    expect($workbook->getSheetNames())->toHaveCount(2);
    expect($workbook->getSheetNames()[1])->toStartWith('HO-10 - ');

    $sections = summarySections($workbook->getSheet(0));

    $codes = collect($sections['participants'])->slice(1)->pluck(0);
    expect($codes)->toContain('JADE-HO-2026-900001')
        ->not->toContain('JADE-HO-2026-900002');

    // The totals block counts the filtered rows, not the whole event.
    $totals = collect($sections['totals'])->map(fn (array $row) => array_slice($row, 0, 4));
    expect($totals->firstWhere(0, 'HO-10'))->toBe(['HO-10', 1, 0, 1]);
    expect($totals->firstWhere(0, 'HO-11'))->toBeNull();
});

test('the export action on the list page returns a download', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');
    actingAs($admin);

    $handsOn = HandsOn::factory()->create();
    HandsOnRegistration::factory()->for($handsOn)->create();

    livewire(ListHandsOnRegistrations::class)
        ->callAction('export')
        ->assertFileDownloaded();
});

test('the export ignores the active tab so every session is included', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');
    actingAs($admin);

    $first = HandsOn::factory()->create(['ho_code' => 'HO-20', 'event_date' => '2026-11-13']);
    $second = HandsOn::factory()->create(['ho_code' => 'HO-21', 'event_date' => '2026-11-14']);

    $firstRegistration = HandsOnRegistration::factory()->for($first)->create([
        'registration_code' => 'JADE-HO-2026-900003',
    ]);
    $secondRegistration = HandsOnRegistration::factory()->for($second)->create([
        'registration_code' => 'JADE-HO-2026-900004',
    ]);

    // The desk is looking at the first session's tab; the export must not inherit it.
    $component = livewire(ListHandsOnRegistrations::class)->set('activeTab', "hands-on-{$first->id}");

    $query = $component->instance()->getTableQueryForExportIgnoringTabs();

    expect($query->pluck('registration_code')->all())
        ->toContain($firstRegistration->registration_code)
        ->toContain($secondRegistration->registration_code);

    $workbook = renderHandsOnExport($query);
    expect($workbook->getSheetNames()[1])->toStartWith('HO-20 - ');
    expect($workbook->getSheetNames()[2])->toStartWith('HO-21 - ');
});
