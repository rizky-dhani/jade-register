<?php

namespace App\Exports;

use App\Exports\Concerns\TextColumns;
use App\Models\Visitor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VisitorExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder, WithHeadings, WithMapping
{
    use TextColumns;

    /**
     * @return array<int, string>
     */
    protected function textHeadings(): array
    {
        return ['Phone'];
    }

    public function collection()
    {
        return Visitor::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Phone',
            'Affiliation',
            'Created At',
            'Updated At',
        ];
    }

    public function map($visitor): array
    {
        return [
            $visitor->id,
            $visitor->name,
            $visitor->email,
            $visitor->phone,
            $visitor->affiliation,
            $visitor->created_at?->format('Y-m-d H:i:s'),
            $visitor->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
