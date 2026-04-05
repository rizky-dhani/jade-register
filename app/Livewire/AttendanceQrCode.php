<?php

namespace App\Livewire;

use App\Models\SeminarRegistration;
use App\Services\QrTokenService;
use Livewire\Component;

class AttendanceQrCode extends Component
{
    public string $token;

    public ?SeminarRegistration $registration = null;

    public bool $isValid = true;

    public bool $isExpired = false;

    protected static string $view = 'livewire.attendance-qr-code';

    public function mount(string $token): void
    {
        $this->token = $token;

        $qrTokenService = app(QrTokenService::class);
        $this->registration = $qrTokenService->validate($token);

        if (! $this->registration) {
            $this->isValid = false;

            return;
        }

        if ($qrTokenService->isExpired($this->registration)) {
            $this->isExpired = true;
            $this->isValid = false;
        }
    }

    public function getQrCodeUrlProperty(): string
    {
        return app(QrTokenService::class)->getVerifyUrl($this->registration);
    }

    public function getPaymentStatusLabelProperty(): string
    {
        return match ($this->registration->payment_status) {
            'verified' => __('seminar.payment_status_verified_label'),
            'pending' => __('seminar.payment_status_pending_label'),
            'rejected' => __('seminar.payment_status_rejected_label'),
            default => __('seminar.payment_status_unknown_label'),
        };
    }

    public function getPaymentStatusColorProperty(): string
    {
        return match ($this->registration->payment_status) {
            'verified' => 'green',
            'pending' => 'yellow',
            'rejected' => 'red',
            default => 'gray',
        };
    }

    public function getHandsOnSessionsProperty()
    {
        return $this->registration->handsOnRegistrations()
            ->with('handsOn')
            ->get()
            ->filter(fn ($reg) => $reg->payment_status === 'verified')
            ->map(fn ($reg) => [
                'name' => $reg->handsOn->name,
                'date' => $reg->handsOn->event_date->format('d M Y'),
                'time' => $reg->handsOn->event_date->format('H:i'),
            ]);
    }

    public function render()
    {
        return view('livewire.attendance-qr-code');
    }
}
