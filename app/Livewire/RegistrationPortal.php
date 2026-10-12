<?php

namespace App\Livewire;

use Illuminate\Support\Facades\App;
use Livewire\Component;

class RegistrationPortal extends Component
{
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
        }
    }

    public function render()
    {
        return view('livewire.registration-portal')->title(__('portal.title').' - '.config('app.name'));
    }
}
