<?php

namespace App\Services;

use App\Exports\HandsOnRegistrationExport;
use App\Exports\SeminarRegistrationExport;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Turns the registration exports into plain rows for Google Sheets.
 *
 * The workbook is rendered through the export classes and read back rather than
 * mapped a second time from the models. That keeps one source of truth: columns,
 * ordering and the hands-on Summary totals block all come from the export, and a
 * change there flows through to the spreadsheet instead of drifting from it.
 */
class RegistrationSheetMirror
{
    /**
     * Export instance => tab-name prefix. Prefixes keep one spreadsheet readable
     * when both exports contribute a tab.
     *
     * @var array<string, class-string>
     */
    private const SOURCES = [
        'Seminar' => SeminarRegistrationExport::class,
        'HandsOn' => HandsOnRegistrationExport::class,
    ];

    /**
     * @return array<int, array{title: string, rows: array<int, array<int, mixed>>}>
     */
    public function tables(): array
    {
        $tables = [];

        foreach (self::SOURCES as $prefix => $export) {
            foreach ($this->tablesFrom(new $export) as $table) {
                $tables[] = [
                    'title' => $this->tabTitle($prefix, $table['title']),
                    'rows' => $table['rows'],
                ];
            }
        }

        return $tables;
    }

    /**
     * @return array<int, array{title: string, rows: array<int, array<int, mixed>>}>
     */
    private function tablesFrom(object $export): array
    {
        $path = tempnam(sys_get_temp_dir(), 'sheets-mirror').'.xlsx';
        file_put_contents($path, Excel::raw($export, ExcelWriter::XLSX));

        try {
            $workbook = IOFactory::load($path);

            $tables = [];

            foreach ($workbook->getAllSheets() as $sheet) {
                $tables[] = ['title' => $sheet->getTitle(), 'rows' => $this->rows($sheet)];
            }

            return $tables;
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    private function rows(Worksheet $sheet): array
    {
        $rows = [];

        foreach ($sheet->toArray(null, true, false, false) as $row) {
            $rows[] = $this->trimTrailingBlanks($row);
        }

        return $rows;
    }

    /**
     * @param  array<int, mixed>  $row
     * @return array<int, mixed>
     */
    private function trimTrailingBlanks(array $row): array
    {
        while ($row !== [] && end($row) === null) {
            array_pop($row);
        }

        return $row;
    }

    private function tabTitle(string $prefix, string $title): string
    {
        // Sheets rejects : \ / ? * [ ] in a title and caps it at 100 characters.
        $clean = str_replace([':', '\\', '/', '?', '*', '[', ']'], '-', $title);

        return mb_substr("{$prefix} {$clean}", 0, 100);
    }
}
