<?php

namespace App\Livewire;

use App\Models\SeminarRegistration as SeminarRegistrationModel;
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
    }

    public function isInternational(): bool
    {
        return $this->registration->country && ! $this->registration->country->is_indonesia;
    }

    public function render()
    {
        return view('livewire.seminar-registration-success');
    }
}
