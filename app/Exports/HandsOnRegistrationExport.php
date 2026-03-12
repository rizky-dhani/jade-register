<?php

namespace App\Exports;

use App\Models\HandsOnRegistration;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class HandsOnRegistrationExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function collection()
    {
        return HandsOnRegistration::with('seminarRegistration', 'handsOn')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Seminar Registration ID',
            'Seminar Registrant Name',
            'Seminar Registrant Email',
            'Hands On ID',
            'Hands On Name',
            'Registration Type',
            'Payment Status',
            'Payment Proof Path',
            'Verified At',
            'Created At',
            'Updated At',
        ];
    }

    public function map($registration): array
    {
        return [
            $registration->id,
            $registration->seminar_registration_id,
            $registration->seminarRegistration?->name,
            $registration->seminarRegistration?->email,
            $registration->hands_on_id,
            $registration->handsOn?->name,
            $registration->registration_type,
            $registration->payment_status,
            $registration->payment_proof_path,
            $registration->verified_at?->format('Y-m-d H:i:s'),
            $registration->created_at?->format('Y-m-d H:i:s'),
            $registration->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
