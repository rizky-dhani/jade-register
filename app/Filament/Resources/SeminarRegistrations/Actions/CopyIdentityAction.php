<?php

namespace App\Filament\Resources\SeminarRegistrations\Actions;

use App\Models\SeminarRegistration;
use Filament\Actions\Action;
use Illuminate\Support\Js;

class CopyIdentityAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('seminar.copy_identity'))
            ->icon('heroicon-o-clipboard-document')
            ->color('gray');

        $this->alpineClickHandler(function (SeminarRegistration $record): string {
            $text = $this->buildIdentityText($record);
            $textJs = Js::from($text);
            $messageJs = Js::from(__('seminar.copy_identity_copied'));
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

    protected function buildIdentityText(SeminarRegistration $record): string
    {
        $lines = [];

        // Personal Information
        $lines[] = '═══ '.strtoupper(__('seminar.personal_information')).' ═══';
        $lines[] = __('seminar.registration_code').': '.$record->registration_code;
        $lines[] = __('seminar.name_plataran').': '.$record->name_license;
        $lines[] = __('seminar.email').': '.$record->email;
        $lines[] = __('seminar.whatsapp_number').': '.($record->phone ?? '—');
        $lines[] = __('seminar.nik').': '.($record->nik ?? '—');
        $lines[] = __('seminar.pdgi_branch').': '.($record->pdgi_branch ?? '—');
        $lines[] = __('seminar.competency').': '.($record->kompetensi ?? '—');

        $isInternational = ! ($record->country?->is_indonesia ?? true);
        if ($isInternational) {
            $lines[] = __('seminar.country').': '.($record->country?->name ?? '—');
        }

        $lines[] = __('seminar.status').': '.match ($record->status) {
            'Dentist' => __('seminar.dentist'),
            'Student' => __('seminar.student'),
            default => $record->status ?? '—',
        };

        // Chosen Package
        $lines[] = '';
        $lines[] = '═══ '.strtoupper(__('seminar.chosen_package')).' ═══';

        $seminar = $record->seminarPackage;
        if ($seminar) {
            $wasEarlyBirdActive = $seminar->early_bird_deadline !== null
                && $record->created_at < $seminar->early_bird_deadline;

            $price = $wasEarlyBirdActive && $seminar->discounted_price
                ? $seminar->formatted_discounted_price.' (Early Bird)'
                : $seminar->formatted_original_price;

            $lines[] = __('seminar.selected_package').': '."{$seminar->name} ({$price})";
        } else {
            $lines[] = __('seminar.selected_package').': '.($record->selected_seminar ?? 'N/A');
        }

        $lines[] = __('seminar.amount').': '.$record->formatted_amount;

        if ($record->addonRegistrations->isNotEmpty()) {
            $lines[] = __('seminar.selected_addons').':';
            foreach ($record->addonRegistrations->loadMissing('addon') as $addonReg) {
                $addon = $addonReg->addon;
                if (! $addon) {
                    continue;
                }
                $addonPrice = $addon->currency === 'USD'
                    ? '$'.number_format($addon->price, 2)
                    : 'Rp '.number_format($addon->price, 0, ',', '.');

                $lines[] = '  • '.$addon->name." ({$addonPrice})";
            }
        }

        $lines[] = __('seminar.hands_on_total_amount').': '.($record->hands_on_total_amount > 0
            ? ($record->currency === 'USD'
                ? '$'.number_format($record->hands_on_total_amount, 2)
                : 'Rp '.number_format($record->hands_on_total_amount, 0, ',', '.'))
            : 'Rp 0');

        if ($record->addonRegistrations->isNotEmpty()) {
            $lines[] = __('seminar.addons_total_amount').': '.($record->currency === 'USD'
                ? '$'.number_format($record->addons_total_amount, 2)
                : 'Rp '.number_format($record->addons_total_amount, 0, ',', '.'));
        }

        $paymentMethod = match ($record->payment_method) {
            'bank_transfer' => __('seminar.bank_transfer'),
            'qris' => __('seminar.qris'),
            default => $record->payment_method ?? '—',
        };
        $lines[] = __('seminar.payment_method').': '.$paymentMethod;

        return implode(PHP_EOL, $lines);
    }
}
