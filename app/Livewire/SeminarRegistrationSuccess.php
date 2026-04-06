<?php

namespace App\Livewire;

use App\Models\SeminarRegistration as SeminarRegistrationModel;
use Illuminate\Support\Facades\App;
use Livewire\Component;

class SeminarRegistrationSuccess extends Component
{
    public ?SeminarRegistrationModel $registration = null;

    public function mount(int $id): void
    {
        $this->registration = SeminarRegistrationModel::find($id);

        if (! $this->registration) {
            abort(404);
        }

        // Set locale based on registration's language preference
        $locale = $this->registration->language ?? 'id';
        $locale = in_array($locale, ['en', 'id']) ? $locale : 'id';
        App::setLocale($locale);
    }

    public function isInternational(): bool
    {
        return $this->registration->country?->is_indonesia === false;
    }

    public function render()
    {
        return view('livewire.seminar-registration-success', [
            'isInternational' => $this->isInternational(),
        ]);
    }
}
