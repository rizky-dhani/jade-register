<?php

namespace App\Livewire;

use App\Services\RegistrationService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ResendConfirmationEmail extends Component
{
    #[Validate('required|email|max:255')]
    public string $email = '';

    public bool $isSuccess = false;

    public ?string $errorMessage = null;

    #[Url(as: 'lang', keep: true)]
    public string $locale = 'id';

    protected $queryString = ['locale'];

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

    public function submit(): void
    {
        $this->resetErrorBag();
        $this->reset('errorMessage');

        $this->validate();

        $key = 'resend-confirmation:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->errorMessage = __('seminar.resend_confirmation_throttle_error');

            return;
        }

        app(RegistrationService::class)->resendConfirmationFor($this->email);

        RateLimiter::hit($key, 60);

        // Always show the same success state: whether or not a registration
        // matched, the response is identical so the form can't be used to
        // probe which emails are registered.
        $this->isSuccess = true;
    }

    public function render()
    {
        return view('livewire.resend-confirmation-email')
            ->title(__('seminar.resend_confirmation_title').' - '.config('app.name'));
    }
}
