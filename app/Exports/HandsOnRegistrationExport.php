<?php

namespace App\Exports;

use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\IValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HandsOnRegistrationExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $handsOnSessions = HandsOn::whereHas('handsOnRegistrations')
            ->orderBy('event_date')
            ->orderBy('ho_code')
            ->get();

        return [
            new HandsOnSummarySheet($this->buildJoinedHandsOnMap()),
            ...$handsOnSessions
                ->map(fn (HandsOn $handsOn) => new HandsOnSessionSheet($handsOn))
                ->all(),
        ];
    }

    /**
     * Map every participant to the comma-separated HO codes of the sessions they joined.
     *
     * @return array<string, string>
     */
    protected function buildJoinedHandsOnMap(): array
    {
        return HandsOnRegistration::with('handsOn')
            ->get()
            ->groupBy(fn (HandsOnRegistration $registration): string => BaseHandsOnSheet::participantKey($registration))
            ->map(fn ($registrations): string => $registrations
                ->sortBy(fn (HandsOnRegistration $registration): string => sprintf(
                    '%s-%s',
                    $registration->handsOn?->event_date?->format('Y-m-d') ?? '',
                    $registration->handsOn?->ho_code ?? '',
                ))
                ->pluck('handsOn.ho_code')
                ->filter()
                ->unique()
                ->implode(', '))
            ->all();
    }
}

abstract class BaseHandsOnSheet implements FromCollection, IValueBinder, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder, WithHeadings, WithMapping, WithStyles, WithTitle
{
    /**
     * Phone numbers arrive as +62…, which PhpSpreadsheet's default binder would
     * store as a number — losing the leading + and rendering as 6.28121E+12.
     */
    public function bindValue(Cell $cell, mixed $value): bool
    {
        if ($cell->getColumn() === $this->phoneColumn()) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return (new DefaultValueBinder)->bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return [$this->phoneColumn() => NumberFormat::FORMAT_TEXT];
    }

    /**
     * Derived from the headings so sheets that insert their own columns stay correct.
     */
    protected function phoneColumn(): string
    {
        $index = array_search('No Telp', $this->headings(), true);

        return Coordinate::stringFromColumnIndex($index === false ? 1 : $index + 1);
    }

    /**
     * Group registrations of the same participant: seminar registration when linked,
     * otherwise the email address, falling back to the row itself.
     */
    public static function participantKey(HandsOnRegistration $registration): string
    {
        if ($registration->seminar_registration_id) {
            return 'seminar-'.$registration->seminar_registration_id;
        }

        if ($registration->email) {
            return 'email-'.mb_strtolower($registration->email);
        }

        return 'registration-'.$registration->id;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function headings(): array
    {
        return [
            'HO Reg Code',
            'Join Seminar?',
            'Seminar Reg Code',
            'Participant Name',
            'Email',
            'No Telp',
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
            $this->participantName($registration),
            $this->participantEmail($registration),
            $this->participantPhone($registration),
            $registration->registration_type === 'combined' ? 'Combined' : 'Independent',
            ucfirst($registration->payment_status ?? '-'),
            $registration->created_at?->format('Y-m-d H:i:s'),
            $registration->verified_at?->format('Y-m-d H:i:s'),
        ];
    }

    protected function participantName(HandsOnRegistration $registration): string
    {
        return $registration->seminarRegistration?->name_license
            ?? $registration->name_license
            ?? $registration->name
            ?? '-';
    }

    protected function participantEmail(HandsOnRegistration $registration): string
    {
        return $registration->seminarRegistration?->email ?? $registration->email ?? '-';
    }

    protected function participantPhone(HandsOnRegistration $registration): string
    {
        return $registration->seminarRegistration?->phone ?? $registration->phone ?? '-';
    }
}

class HandsOnSummarySheet extends BaseHandsOnSheet implements WithEvents
{
    /**
     * @param  array<string, string>  $joinedHandsOn
     */
    public function __construct(
        protected array $joinedHandsOn,
    ) {}

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $this->writeTotalsTable($event->sheet->getDelegate());
            },
        ];
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function collection()
    {
        return HandsOnRegistration::with('seminarRegistration')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->unique(fn (HandsOnRegistration $registration): string => self::participantKey($registration))
            ->reverse()
            ->values();
    }

    public function headings(): array
    {
        $headings = parent::headings();
        array_splice($headings, 3, 0, ['Joined Hands On']);

        return $headings;
    }

    public function map($registration): array
    {
        $row = parent::map($registration);
        array_splice($row, 3, 0, [$this->joinedHandsOn[self::participantKey($registration)] ?? '-']);

        return $row;
    }

    /**
     * Prepend a per-session participant count table above the participant list.
     * Paid counts verified payments, Pending counts pending payments; Total is their sum.
     */
    protected function writeTotalsTable(Worksheet $sheet): void
    {
        $sessions = HandsOn::whereHas('handsOnRegistrations')
            ->withCount([
                'handsOnRegistrations as paid_count' => fn ($query) => $query->where('payment_status', 'verified'),
                'handsOnRegistrations as pending_count' => fn ($query) => $query->where('payment_status', 'pending'),
            ])
            ->orderBy('event_date')
            ->orderBy('ho_code')
            ->get();

        $headerRow = 1;
        $lastRow = $headerRow + $sessions->count();

        $sheet->insertNewRowBefore($headerRow, $lastRow + 1);
        $sheet->fromArray(['HO Code', 'Paid', 'Pending', 'Total'], null, 'A'.$headerRow, true);
        $sheet->getStyle('A'.$headerRow.':D'.$headerRow)->getFont()->setBold(true);

        $row = $headerRow + 1;

        foreach ($sessions as $session) {
            $paid = (int) $session->paid_count;
            $pending = (int) $session->pending_count;

            $sheet->fromArray([$session->ho_code, $paid, $pending, $paid + $pending], null, 'A'.$row, true);
            $row++;
        }

        $sheet->getStyle('A'.$headerRow.':D'.$lastRow)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
}

class HandsOnSessionSheet extends BaseHandsOnSheet
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
}
