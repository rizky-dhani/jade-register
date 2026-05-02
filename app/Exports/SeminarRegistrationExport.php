<?php

namespace App\Exports;

use App\Models\SeminarRegistration;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SeminarRegistrationExport implements WithMultipleSheets
{
    public function __construct(
        protected ?string $paymentMethod = null,
    ) {}

    public function sheets(): array
    {
        return [
            new LocalParticipantsSheet($this->paymentMethod),
            new InternationalParticipantsSheet($this->paymentMethod),
        ];
    }
}

abstract class BaseParticipantsSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    public function __construct(
        protected ?string $paymentMethod = null,
    ) {}

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    protected function getSelectedPrice(SeminarRegistration $registration): string
    {
        $seminar = $registration->seminarPackage;
        if (! $seminar) {
            return '-';
        }

        $wasEarlyBirdActive = $seminar->early_bird_deadline !== null
            && $registration->created_at < $seminar->early_bird_deadline;

        if ($wasEarlyBirdActive && $seminar->discounted_price) {
            return $seminar->formatted_discounted_price.' (Early Bird)';
        }

        return $seminar->formatted_original_price ?? '-';
    }

    protected function getPaymentMethodLabel(?string $method): string
    {
        return match ($method) {
            'bank_transfer' => 'Transfer Bank',
            'qris' => 'QRIS',
            default => $method ?? '-',
        };
    }
}

class LocalParticipantsSheet extends BaseParticipantsSheet
{
    public function collection()
    {
        return SeminarRegistration::with('country', 'seminarPackage')
            ->where(function ($query) {
                $query->whereHas('country', function ($q) {
                    $q->where('name', 'Indonesia');
                })
                    ->orWhereNull('country_id');
            })
            ->when($this->paymentMethod, fn ($query, $method) => $query->where('payment_method', $method))
            ->get();
    }

    public function headings(): array
    {
        return [
            'Kode Peserta',
            'Nama sesuai Plataran Sehat',
            'Email',
            'NIK',
            'Cabang PDGI',
            'Seminar',
            'Harga Paket Seminar',
            'Status Pembayaran',
            'Kompetensi',
            'Metode Pembayaran',
            'Ingin Ikut Hands On?',
        ];
    }

    public function map($registration): array
    {
        return [
            $registration->registration_code,
            $registration->name_license,
            $registration->email,
            $registration->nik,
            $registration->pdgi_branch,
            $registration->seminarPackage?->name ?? $registration->selected_seminar,
            $this->getSelectedPrice($registration),
            ucfirst($registration->payment_status),
            $registration->kompetensi,
            $this->getPaymentMethodLabel($registration->payment_method),
            $registration->wants_hands_on ? 'Ya' : 'Tidak',
        ];
    }
}

class InternationalParticipantsSheet extends BaseParticipantsSheet
{
    public function collection()
    {
        return SeminarRegistration::with('country', 'seminarPackage')
            ->whereHas('country', function ($query) {
                $query->where('name', '!=', 'Indonesia');
            })
            ->when($this->paymentMethod, fn ($query, $method) => $query->where('payment_method', $method))
            ->get();
    }

    public function headings(): array
    {
        return [
            'Kode Peserta',
            'Nama',
            'Email',
            'No Telp',
            'Status',
            'Negara',
            'Seminar',
            'Metode Pembayaran',
            'Harga Paket Seminar',
            'Ingin Ikut Hands On?',
        ];
    }

    public function map($registration): array
    {
        return [
            $registration->registration_code,
            $registration->name,
            $registration->email,
            $registration->phone,
            $this->getStatusLabel($registration->status),
            $registration->country?->name ?? '-',
            $registration->seminarPackage?->name ?? $registration->selected_seminar,
            $this->getPaymentMethodLabel($registration->payment_method),
            $this->getSelectedPrice($registration),
            $registration->wants_hands_on ? 'Ya' : 'Tidak',
        ];
    }

    protected function getStatusLabel(string $status): string
    {
        return match ($status) {
            'Dentist' => __('seminar.dentist'),
            'Student' => __('seminar.student'),
            default => $status,
        };
    }
}
