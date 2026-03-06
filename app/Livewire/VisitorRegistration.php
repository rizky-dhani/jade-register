<?php

namespace App\Livewire;

use App\Models\Visitor;
use App\Services\RegistrationService;
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

        $registrationService = app(RegistrationService::class);
        $registrationService->sendVisitorConfirmation($this->visitor);

        $this->isSuccess = true;
    }
}
