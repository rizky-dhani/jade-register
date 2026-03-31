<?php

namespace App\Livewire;

use App\Models\Visitor;
use App\Services\RegistrationService;
use App\Services\VisitorQrTokenService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\App;
use Livewire\Attributes\Url;
use Livewire\Component;

class VisitorRegistration extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $affiliation = '';

    public bool $isSuccess = false;

    public bool $isSubmitting = false;

    public ?Visitor $visitor = null;

    #[Url(as: 'lang', keep: true)]
    public string $locale = 'id';

    protected $queryString = ['locale'];

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email',
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
        // Prevent concurrent submissions
        if ($this->isSubmitting || $this->isSuccess) {
            return;
        }

        $this->isSubmitting = true;

        $this->validate();

        // Check if email already exists (prevents duplicate creation)
        $existingVisitor = Visitor::where('email', $this->email)->first();

        if ($existingVisitor) {
            $this->visitor = $existingVisitor;
            $this->isSuccess = true;
            $this->isSubmitting = false;

            return;
        }

        try {
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
        } catch (QueryException $e) {
            // Handle unique constraint violation - another request created it
            if (str_contains($e->getMessage(), 'visitors_email_unique')) {
                $this->visitor = Visitor::where('email', $this->email)->first();
                $this->isSuccess = true;
            }
        } finally {
            $this->isSubmitting = false;
        }
    }

    public function getQrCodeUrlProperty(): ?string
    {
        if (! $this->visitor) {
            return null;
        }

        return app(VisitorQrTokenService::class)->getVerifyUrl($this->visitor);
    }
}
