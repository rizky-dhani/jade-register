<?php

namespace App\Livewire;

use App\Models\Visitor;
use App\Services\VisitorQrTokenService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class VisitorVerify extends Component
{
    public string $token;

    public ?Visitor $visitor = null;

    public bool $isValid = true;

    public bool $isAlreadyScanned = false;

    public bool $showSuccess = false;

    protected static string $view = 'livewire.visitor-verify';

    public function mount(string $token): void
    {
        $this->token = $token;

        $qrTokenService = app(VisitorQrTokenService::class);
        $this->visitor = $qrTokenService->validate($token);

        if (! $this->visitor) {
            $this->isValid = false;

            return;
        }

        if ($this->visitor->isScanned()) {
            $this->isAlreadyScanned = true;
        }
    }

    public function confirmAttendance(): void
    {
        if (! Auth::check()) {
            $this->addError('auth', __('seminar.login_required'));

            return;
        }

        if (! Auth::user()->hasRole('Admin')) {
            $this->addError('auth', __('seminar.admin_only'));

            return;
        }

        if ($this->visitor->isScanned()) {
            $this->isAlreadyScanned = true;

            return;
        }

        $this->visitor->markAsScanned();
        $this->isAlreadyScanned = true;
        $this->showSuccess = true;
    }

    public function getScannedAtProperty(): ?string
    {
        return $this->visitor->scanned_at?->format('d M Y H:i');
    }

    public function render()
    {
        return view('livewire.visitor-verify');
    }
}
