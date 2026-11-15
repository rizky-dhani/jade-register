<?php

namespace App\Filament\Resources\HandsOnRegistrations\Actions;

use App\Models\HandsOnRegistration;
use Filament\Actions\Action;
use Illuminate\Support\Js;

class CopyParticipantDataAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'copyParticipantData';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('seminar.copy_participant_data'))
            ->icon('heroicon-o-clipboard-document')
            ->color('gray');

        $this->alpineClickHandler(function (HandsOnRegistration $record): string {
            $text = static::buildParticipantText($record);
            $textJs = Js::from($text);
            $messageJs = Js::from(__('seminar.copy_participant_data_copied'));
            $durationJs = Js::from(2000);

            return <<<JS
                window.navigator.clipboard.writeText({$textJs})
                \$tooltip({$messageJs}, {
                    theme: \$store.theme,
                    timeout: {$durationJs},
                })
                JS;
        });
    }

    /**
     * A hands-on row often carries no personal data of its own: the participant
     * identity lives on the linked seminar registration, so that row wins and
     * the hands-on row is only the fallback.
     */
    public static function buildParticipantText(HandsOnRegistration $record): string
    {
        $seminar = $record->seminarRegistration;

        $value = fn (?string $seminarValue, ?string $ownValue): string => filled($seminarValue)
            ? (string) $seminarValue
            : (filled($ownValue) ? (string) $ownValue : '—');

        $country = filled($seminar?->country_id) ? $seminar->country : $record->country;
        $status = $seminar?->status ?: $record->status;

        $lines = [];
        $lines[] = '═══ '.strtoupper(__('seminar.personal_information')).' ═══';
        $lines[] = __('seminar.registration_code').': '.$record->registration_code;

        if ($seminar) {
            $lines[] = __('seminar.seminar_registration').': '.$seminar->registration_code;
        }

        $lines[] = __('seminar.name_plataran').': '.$value($seminar?->name_license, $record->name_license ?: $record->name);
        $lines[] = __('seminar.email').': '.$value($seminar?->email, $record->email);
        $lines[] = __('seminar.whatsapp_number').': '.$value($seminar?->phone, $record->phone);
        $lines[] = __('seminar.nik').': '.$value($seminar?->nik, $record->nik);
        $lines[] = __('seminar.pdgi_branch').': '.$value($seminar?->pdgi_branch, $record->pdgi_branch);
        $lines[] = __('seminar.competency').': '.$value($seminar?->kompetensi, $record->kompetensi);

        if (! ($country?->is_indonesia ?? true)) {
            $lines[] = __('seminar.country').': '.($country?->name ?? '—');
        }

        $lines[] = __('seminar.status').': '.match ($status) {
            'Dentist' => __('seminar.dentist'),
            'Student' => __('seminar.student'),
            default => $status ?? '—',
        };

        $handsOn = $record->handsOn;
        if ($handsOn) {
            $lines[] = '';
            $lines[] = '═══ '.strtoupper(__('seminar.hands_on_session')).' ═══';
            $lines[] = collect([
                $handsOn->name,
                $handsOn->event_date?->format('d M Y'),
                $handsOn->doctor_name,
            ])->filter()->implode(' - ');
        }

        return implode(PHP_EOL, $lines);
    }
}
