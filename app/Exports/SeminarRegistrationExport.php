<?php

namespace App\Exports;

use App\Models\SeminarRegistration;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SeminarRegistrationExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new LocalParticipantsSheet,
            new InternationalParticipantsSheet,
        ];
    }
}

class LocalParticipantsSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return SeminarRegistration::with('country', 'user')
            ->whereHas('country', function ($query) {
                $query->where('name', 'Indonesia');
            })
            ->orWhereNull('country_id')
            ->get();
    }

    public function headings(): array
    {
        return $this->getHeadings();
    }

    public function map($registration): array
    {
        return $this->mapData($registration);
    }

    protected function getHeadings(): array
    {
        return [
            'ID',
            'Registration Code',
            'Email',
            'Name',
            'Name (License)',
            'NIK',
            'PDGI Branch',
            'Kompetensi',
            'Phone',
            'Country',
            'Language',
            'Registration Type',
            'Selected Seminar',
            'Payment Method',
            'Amount',
            'Currency',
            'Payment Status',
            'Rejection Reason',
            'Verified By',
            'Verified At',
            'Wants Poster Competition',
            'Wants Hands On',
            'Hands On Total Amount',
            'Status',
            'User ID',
            'Created At',
            'Updated At',
        ];
    }

    protected function mapData($registration): array
    {
        return [
            $registration->id,
            $registration->registration_code,
            $registration->email,
            $registration->name,
            $registration->name_license,
            $registration->nik,
            $registration->pdgi_branch,
            $registration->kompetensi,
            $registration->phone,
            $registration->country?->name,
            $registration->language,
            $registration->registration_type,
            $registration->selected_seminar,
            $registration->payment_method,
            $registration->amount,
            $registration->currency,
            $registration->payment_status,
            $registration->rejection_reason,
            $registration->verified_by,
            $registration->verified_at?->format('Y-m-d H:i:s'),
            $registration->wants_poster_competition ? 'Yes' : 'No',
            $registration->wants_hands_on ? 'Yes' : 'No',
            $registration->hands_on_total_amount,
            $registration->status,
            $registration->user_id,
            $registration->created_at?->format('Y-m-d H:i:s'),
            $registration->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

class InternationalParticipantsSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return SeminarRegistration::with('country', 'user')
            ->whereHas('country', function ($query) {
                $query->where('name', '!=', 'Indonesia');
            })
            ->get();
    }

    public function headings(): array
    {
        return (new LocalParticipantsSheet)->getHeadings();
    }

    public function map($registration): array
    {
        return (new LocalParticipantsSheet)->mapData($registration);
    }
}
