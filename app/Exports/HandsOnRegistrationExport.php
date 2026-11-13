<?php

namespace App\Exports;

use App\Exports\Concerns\TextColumns;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * The workbook mirrors the desk list: when the list page hands over its filtered
 * query, the sheets and the Summary totals cover exactly those rows. Callers
 * without a query (the Google Sheets mirror) still get every registration.
 */
class HandsOnRegistrationExport implements WithMultipleSheets
{
    public function __construct(
        protected ?Builder $query = null,
    ) {}

    public function sheets(): array
    {
        return [
            new HandsOnSummarySheet($this->buildJoinedHandsOnMap(), $this->query),
            ...$this->sessions()
                ->map(fn (HandsOn $handsOn) => new HandsOnSessionSheet($handsOn, $this->query))
                ->all(),
        ];
    }

    /**
     * The list page's filtered query, or every registration when the export runs
     * outside that page.
     */
    protected function baseQuery(): Builder
    {
        return $this->query ? clone $this->query : HandsOnRegistration::query();
    }

    /**
     * Sessions that still have a row after filtering, so a filtered export does
     * not ship empty sheets.
     *
     * @return Collection<int, HandsOn>
     */
    protected function sessions(): Collection
    {
        if (! $this->query) {
            return HandsOn::whereHas('handsOnRegistrations')
                ->orderBy('event_date')
                ->orderBy('ho_code')
                ->get();
        }

        $ids = (clone $this->query)
            ->reorder()
            ->distinct()
            ->pluck('hands_on_registrations.hands_on_id');

        return HandsOn::whereIn('id', $ids)
            ->orderBy('event_date')
            ->orderBy('ho_code')
            ->get();
    }

    /**
     * Map every participant to the comma-separated HO codes of the sessions they joined.
     *
     * @return array<string, string>
     */
    protected function buildJoinedHandsOnMap(): array
    {
        return $this->baseQuery()
            ->reorder()
            ->with('handsOn')
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

abstract class BaseHandsOnSheet implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use TextColumns;

    protected ?Builder $query = null;

    /**
     * The list page's filtered query, or every registration when the export runs
     * outside that page. Each sheet clones it, so the sheets never share state.
     */
    protected function baseQuery(): Builder
    {
        return $this->query ? clone $this->query : HandsOnRegistration::query();
    }

    /**
     * @return array<int, string>
     */
    protected function textHeadings(): array
    {
        return ['No Telp'];
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
        ?Builder $query = null,
    ) {
        $this->query = $query;
    }

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
        // reorder() drops the list page's sort. The relative ordering here decides
        // which registration represents a participant, so it must stay fixed.
        return $this->baseQuery()
            ->with('seminarRegistration')
            ->reorder()
            ->orderByDesc('hands_on_registrations.created_at')
            ->orderByDesc('hands_on_registrations.id')
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
        $sessions = $this->sessionTotals();

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

    /**
     * Paid/pending counts per session, restricted to the filtered rows so the
     * block agrees with the participant list it sits above.
     *
     * @return Collection<int, object{ho_code: ?string, paid_count: int, pending_count: int}>
     */
    protected function sessionTotals(): Collection
    {
        if (! $this->query) {
            return HandsOn::whereHas('handsOnRegistrations')
                ->withCount([
                    'handsOnRegistrations as paid_count' => fn ($query) => $query->where('payment_status', 'verified'),
                    'handsOnRegistrations as pending_count' => fn ($query) => $query->where('payment_status', 'pending'),
                ])
                ->orderBy('event_date')
                ->orderBy('ho_code')
                ->get();
        }

        $counts = HandsOnRegistration::query()
            ->whereIn('id', (clone $this->query)->reorder()->pluck('hands_on_registrations.id'))
            ->selectRaw('hands_on_registrations.hands_on_id as hands_on_id')
            ->selectRaw("SUM(CASE WHEN hands_on_registrations.payment_status = 'verified' THEN 1 ELSE 0 END) as paid_count")
            ->selectRaw("SUM(CASE WHEN hands_on_registrations.payment_status = 'pending' THEN 1 ELSE 0 END) as pending_count")
            ->groupBy('hands_on_registrations.hands_on_id')
            ->get()
            ->keyBy('hands_on_id');

        return HandsOn::whereIn('id', $counts->keys())
            ->orderBy('event_date')
            ->orderBy('ho_code')
            ->get()
            ->map(fn (HandsOn $session): object => (object) [
                'ho_code' => $session->ho_code,
                'paid_count' => (int) ($counts->get($session->id)?->paid_count ?? 0),
                'pending_count' => (int) ($counts->get($session->id)?->pending_count ?? 0),
            ]);
    }
}

class HandsOnSessionSheet extends BaseHandsOnSheet
{
    public function __construct(
        protected HandsOn $handsOn,
        ?Builder $query = null,
    ) {
        $this->query = $query;
    }

    public function collection()
    {
        return $this->baseQuery()
            ->with('seminarRegistration')
            ->reorder()
            ->where('hands_on_registrations.hands_on_id', $this->handsOn->id)
            ->orderBy('hands_on_registrations.created_at')
            ->orderBy('hands_on_registrations.id')
            ->get();
    }

    public function title(): string
    {
        $title = "{$this->handsOn->ho_code} - {$this->handsOn->name}";

        return mb_substr(str_replace([':', '\\', '/', '?', '*', '[', ']'], '-', $title), 0, 31);
    }
}
