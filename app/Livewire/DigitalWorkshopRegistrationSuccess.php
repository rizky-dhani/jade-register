<?php

namespace App\Livewire;

use App\Models\DigitalWorkshopRegistration;
use Illuminate\Support\Facades\App;
use Livewire\Component;

class DigitalWorkshopRegistrationSuccess extends Component
{
    protected static string $view = 'livewire.digital-workshop-registration-success';

    public DigitalWorkshopRegistration $registration;

    public string $locale = 'id';

    public function mount(int $id): void
    {
        $registration = DigitalWorkshopRegistration::with('digitalWorkshop')->find($id);

        if (! $registration) {
            abort(404);
        }

        $this->registration = $registration;

        $this->locale = in_array($registration->language, ['en', 'id']) ? $registration->language : 'id';
        App::setLocale($this->locale);
    }

    public function render()
    {
        return view('livewire.digital-workshop-registration-success')
            ->title(__('seminar.digital_workshop_success_title').' - '.config('app.name'));
    }
}
