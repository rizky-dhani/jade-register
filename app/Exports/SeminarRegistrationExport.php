<?php

namespace App\Exports;

use App\Models\SeminarRegistration;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SeminarRegistrationExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function collection()
    {
        return SeminarRegistration::with('country', 'user')->get();
    }

    public function headings(): array
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
            'Pricing Tier',
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

    public function map($registration): array
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
            $registration->pricing_tier,
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
