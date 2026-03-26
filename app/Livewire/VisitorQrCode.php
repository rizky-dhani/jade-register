<?php

namespace App\Livewire;

use App\Models\Visitor;
use App\Services\VisitorQrTokenService;
use Livewire\Component;

class VisitorQrCode extends Component
{
    public string $token;

    public ?Visitor $visitor = null;

    public bool $isValid = true;

    protected static string $view = 'livewire.visitor-qr-code';

    public function mount(string $token): void
    {
        $this->token = $token;

        $qrTokenService = app(VisitorQrTokenService::class);
        $this->visitor = $qrTokenService->validate($token);

        if (! $this->visitor) {
            $this->isValid = false;

            return;
        }
    }

    public function getQrCodeUrlProperty(): string
    {
        return app(VisitorQrTokenService::class)->getVerifyUrl($this->visitor);
    }

    public function getScannedStatusProperty(): string
    {
        return $this->visitor->isScanned()
            ? __('seminar.visitor_qr_scanned')
            : __('seminar.visitor_qr_not_scanned');
    }

    public function getScannedStatusColorProperty(): string
    {
        return $this->visitor->isScanned() ? 'red' : 'green';
    }

    public function render()
    {
        return view('livewire.visitor-qr-code');
    }
}
