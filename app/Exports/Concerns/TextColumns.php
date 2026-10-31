<?php

namespace App\Exports\Concerns;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * Keeps the columns named by textHeadings() as text. PhpSpreadsheet's default
 * binder stores a phone number or a 16-digit NIK as a number, which drops a
 * leading + and renders as 6.28121E+12 in Excel.
 *
 * The column letters come from headings(), so a sheet that inserts its own
 * columns still resolves them correctly.
 */
trait TextColumns
{
    /**
     * Headings whose values must be written as text, e.g. ['No Telp', 'NIK'].
     * Headings absent from a given sheet are simply ignored.
     *
     * @return array<int, string>
     */
    abstract protected function textHeadings(): array;

    public function bindValue(Cell $cell, mixed $value): bool
    {
        if (in_array($cell->getColumn(), $this->textColumns(), true)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return (new DefaultValueBinder)->bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return array_fill_keys($this->textColumns(), NumberFormat::FORMAT_TEXT);
    }

    /**
     * @return array<int, string>
     */
    protected function textColumns(): array
    {
        $columns = [];

        foreach ($this->headings() as $index => $heading) {
            if (in_array($heading, $this->textHeadings(), true)) {
                $columns[] = Coordinate::stringFromColumnIndex($index + 1);
            }
        }

        return $columns;
    }
}
