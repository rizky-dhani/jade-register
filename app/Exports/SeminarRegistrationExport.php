<?php

namespace App\Exports;

use App\Exports\Concerns\TextColumns;
use App\Models\SeminarRegistration;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * The workbook mirrors the desk list: when the list page hands over its filtered
 * query, the sheets cover exactly those rows. Callers without a query (the Google
 * Sheets mirror) still get every registration.
 */
class SeminarRegistrationExport implements WithMultipleSheets
{
    public function __construct(
        protected ?string $paymentMethod = null,
        protected ?Builder $query = null,
    ) {}

    public function sheets(): array
    {
        return [
            new LocalParticipantsSheet($this->paymentMethod, $this->query),
            new InternationalParticipantsSheet($this->paymentMethod, $this->query),
        ];
    }
}

abstract class BaseParticipantsSheet implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use Exportable;
    use TextColumns;

    /**
     * @return array<int, string>
     */
    protected function textHeadings(): array
    {
        return ['No Telp', 'NIK'];
    }

    public function __construct(
        protected ?string $paymentMethod = null,
        protected ?Builder $query = null,
    ) {}

    /**
     * The list page's filtered query, or every registration when the export runs
     * outside that page. Each sheet clones it, so the sheets never share state.
     */
    protected function baseQuery(): Builder
    {
        return $this->query ? clone $this->query : SeminarRegistration::query();
    }

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
        // reorder() drops the list page's sort: which rows are exported follows the
        // filters, but the workbook keeps its own stable ordering.
        return $this->baseQuery()
            ->with('country', 'seminarPackage')
            ->reorder()
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
            'No Telp',
            'NIK',
            'Cabang PDGI',
            'Seminar',
            'Harga Paket Seminar',
            'Status Pembayaran',
            'Kompetensi',
            'Metode Pembayaran',
            'Tanggal Daftar',
            'Ingin Ikut Hands On?',
        ];
    }

    public function title(): string
    {
        return 'Local';
    }

    public function map($registration): array
    {
        return [
            $registration->registration_code,
            $registration->name_license,
            $registration->email,
            $registration->phone,
            $registration->nik,
            $registration->pdgi_branch,
            $registration->seminarPackage?->name ?? $registration->selected_seminar,
            $this->getSelectedPrice($registration),
            ucfirst($registration->payment_status),
            $registration->kompetensi,
            $this->getPaymentMethodLabel($registration->payment_method),
            $registration->created_at?->format('Y-m-d H:i:s'),
            $registration->wants_hands_on ? 'Ya' : 'Tidak',
        ];
    }
}

class InternationalParticipantsSheet extends BaseParticipantsSheet
{
    public function collection()
    {
        return $this->baseQuery()
            ->with('country', 'seminarPackage')
            ->reorder()
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
            'Tanggal Daftar',
            'Ingin Ikut Hands On?',
        ];
    }

    public function title(): string
    {
        return 'International';
    }

    public function map($registration): array
    {
        return [
            $registration->registration_code,
            $registration->name_license,
            $registration->email,
            $registration->phone,
            $this->getStatusLabel($registration->status),
            $registration->country?->name ?? '-',
            $registration->seminarPackage?->name ?? $registration->selected_seminar,
            $this->getPaymentMethodLabel($registration->payment_method),
            $this->getSelectedPrice($registration),
            $registration->created_at?->format('Y-m-d H:i:s'),
            $registration->wants_hands_on ? 'Ya' : 'Tidak',
        ];
    }

    /**
     * status is nullable in the schema, so a row without one must not throw.
     */
    protected function getStatusLabel(?string $status): string
    {
        return match ($status) {
            'Dentist' => __('seminar.dentist'),
            'Student' => __('seminar.student'),
            null, '' => '-',
            default => $status,
        };
    }
}
