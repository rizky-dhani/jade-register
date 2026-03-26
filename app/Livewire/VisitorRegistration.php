<?php

namespace App\Livewire;

use App\Models\Visitor;
use App\Services\RegistrationService;
use App\Services\VisitorQrTokenService;
use Livewire\Component;

class VisitorRegistration extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $affiliation = '';

    public bool $isSuccess = false;

    public ?Visitor $visitor = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:visitors,email',
        'phone' => 'required|string|max:20',
        'affiliation' => 'nullable|string|max:255',
    ];

    public function render()
    {
        return view('livewire.visitor-registration');
    }

    public function submit()
    {
        $this->validate();

        $this->visitor = Visitor::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'affiliation' => $this->affiliation,
        ]);

        // Generate QR token for the visitor
        $qrTokenService = app(VisitorQrTokenService::class);
        $qrTokenService->generate($this->visitor);

        $registrationService = app(RegistrationService::class);
        $registrationService->sendVisitorConfirmation($this->visitor);

        $this->isSuccess = true;
    }

    public function getQrCodeUrlProperty(): ?string
    {
        if (! $this->visitor) {
            return null;
        }

        return app(VisitorQrTokenService::class)->getVerifyUrl($this->visitor);
    }
}
