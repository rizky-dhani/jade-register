<?php

namespace App\Livewire;

use App\Models\MarketingSource;
use App\Models\Profession;
use App\Models\Visitor;
use App\Services\RegistrationService;
use Livewire\Component;
use Livewire\WithFileUploads;

class VisitorRegistration extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $affiliation = '';

    public string $profession = '';

    public string $preferred_visit_date = '';

    public string $marketing_source = '';

    public bool $isSuccess = false;

    public ?Visitor $visitor = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:visitors,email',
        'phone' => 'required|string|max:20',
        'affiliation' => 'nullable|string|max:255',
        'profession' => 'required|string',
        'preferred_visit_date' => 'required|date|in:2026-11-13,2026-11-14,2026-11-15',
        'marketing_source' => 'nullable|string',
    ];

    public function mount()
    {
        $this->preferred_visit_date = '2026-11-13';
    }

    public function render()
    {
        return view('livewire.visitor-registration', [
            'professions' => Profession::ordered()->get(),
            'marketingSources' => MarketingSource::ordered()->get(),
        ]);
    }

    public function submit()
    {
        $this->validate();

        $this->visitor = Visitor::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'affiliation' => $this->affiliation,
            'profession' => $this->profession,
            'preferred_visit_date' => $this->preferred_visit_date,
            'marketing_source' => $this->marketing_source,
        ]);

        $registrationService = app(RegistrationService::class);
        $registrationService->sendVisitorConfirmation($this->visitor);

        $this->isSuccess = true;
    }
}
