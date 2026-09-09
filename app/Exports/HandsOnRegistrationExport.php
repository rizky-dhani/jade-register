<?php

namespace App\Exports;

use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HandsOnRegistrationExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return HandsOn::whereHas('handsOnRegistrations')
            ->orderBy('event_date')
            ->orderBy('ho_code')
            ->get()
            ->map(fn (HandsOn $handsOn) => new HandsOnSessionSheet($handsOn))
            ->all();
    }
}

class HandsOnSessionSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected HandsOn $handsOn,
    ) {}

    public function collection()
    {
        return HandsOnRegistration::with('seminarRegistration')
            ->where('hands_on_id', $this->handsOn->id)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    public function title(): string
    {
        $title = "{$this->handsOn->ho_code} - {$this->handsOn->name}";

        return mb_substr(str_replace([':', '\\', '/', '?', '*', '[', ']'], '-', $title), 0, 31);
    }

    public function headings(): array
    {
        return [
            'HO Registration Code',
            'Seminar Registration',
            'Seminar Reg Code',
            'Participant Name',
            'Email',
            'Register Type',
            'Payment Status',
            'Created At',
            'Verified At',
        ];
    }

    public function map($registration): array
    {
        return [
            $registration->registration_code ?? '-',
            $registration->registration_type === 'combined' ? 'Yes' : 'No',
            $registration->seminarRegistration?->registration_code ?? '-',
            $registration->seminarRegistration?->name_license
                ?? $registration->seminarRegistration?->name
                ?? $registration->name_license
                ?? $registration->name
                ?? '-',
            $registration->seminarRegistration?->email ?? $registration->email ?? '-',
            $registration->registration_type === 'combined' ? 'Combined' : 'Independent',
            ucfirst($registration->payment_status ?? '-'),
            $registration->created_at?->format('Y-m-d H:i:s'),
            $registration->verified_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
