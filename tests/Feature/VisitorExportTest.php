<?php

use App\Exports\VisitorExport;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

uses(RefreshDatabase::class);

function renderVisitorExport(): Spreadsheet
{
    $path = tempnam(sys_get_temp_dir(), 'visitor-export').'.xlsx';
    file_put_contents($path, Excel::raw(new VisitorExport, ExcelWriter::XLSX));

    $workbook = IOFactory::load($path);
    unlink($path);

    return $workbook;
}

/**
 * Non-empty values in one column, each paired with the type PhpSpreadsheet stored it as.
 *
 * @return array<int, array{0: string, 1: string}>
 */
function visitorColumnValues(Worksheet $sheet, string $column): array
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

test('phone numbers are stored as text so excel does not show scientific notation', function () {
    Visitor::create([
        'name' => 'Visitor One',
        'email' => 'visitor@example.com',
        'phone' => '+6281200000001',
    ]);

    expect(visitorColumnValues(renderVisitorExport()->getSheet(0), 'D'))
        ->toBe([['Phone', 's'], ['+6281200000001', 's']]);
});
