<?php

namespace App\Livewire;

use App\Models\Visitor;
use App\Services\RegistrationService;
use App\Services\VisitorQrTokenService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Illuminate\Support\Facades\App;
use Livewire\Attributes\Url;
use Livewire\Component;

class VisitorRegistration extends Component
{
    use WithRateLimiting;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $affiliation = '';

    public bool $isSuccess = false;

    public ?Visitor $visitor = null;

    #[Url(as: 'lang', keep: true)]
    public string $locale = 'id';

    protected $queryString = ['locale'];

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:visitors,email',
        'phone' => 'required|string|max:20',
        'affiliation' => 'nullable|string|max:255',
    ];

    public function mount(): void
    {
        $this->locale = in_array($this->locale, ['en', 'id']) ? $this->locale : 'id';
        App::setLocale($this->locale);
    }

    public function setLocale(string $locale): void
    {
        if (in_array($locale, ['en', 'id'])) {
            $this->locale = $locale;
            App::setLocale($locale);
            $this->dispatch('locale-changed', locale: $locale);
        }
    }

    public function updatedLocale(): void
    {
        $this->locale = in_array($this->locale, ['en', 'id']) ? $this->locale : 'id';
        App::setLocale($this->locale);
    }

    public function render()
    {
        return view('livewire.visitor-registration');
    }

    public function submit()
    {
        // Prevent double submission - check if already successful
        if ($this->isSuccess) {
            return;
        }

        // Rate limit to prevent duplicate requests
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            return;
        }

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
