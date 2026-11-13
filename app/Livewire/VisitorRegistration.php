<?php

namespace App\Livewire;

use App\Models\Visitor;
use App\Services\RegistrationService;
use Illuminate\Support\Facades\App;
use Livewire\Attributes\Url;
use Livewire\Component;

class VisitorRegistration extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $affiliation = '';

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
        return view('livewire.visitor-registration')->title(__('seminar.visitor_registration_title').' - '.config('app.name'));
    }

    public function submit()
    {
        $this->validate();

        $visitor = Visitor::firstOrCreate(
            ['email' => $this->email],
            [
                'name' => $this->name,
                'phone' => $this->phone,
                'affiliation' => $this->affiliation,
            ]
        );

        // Only send the confirmation email for new visitors
        if ($visitor->wasRecentlyCreated) {
            app(RegistrationService::class)->sendVisitorConfirmation($visitor);
        }

        $this->redirectRoute('register.visitor.success', [
            'id' => $visitor->id,
            'lang' => $this->locale,
        ], navigate: true);
    }
}
