<?php

namespace App\Livewire;

use App\Models\Visitor;
use Illuminate\Support\Facades\App;
use Livewire\Attributes\Url;
use Livewire\Component;

class VisitorRegistrationSuccess extends Component
{
    public ?Visitor $visitor = null;

    #[Url(as: 'lang', keep: true)]
    public string $locale = 'id';

    public function mount(int $id): void
    {
        $this->visitor = Visitor::find($id);

        if (! $this->visitor) {
            abort(404);
        }

        $this->locale = in_array($this->locale, ['en', 'id']) ? $this->locale : 'id';
        App::setLocale($this->locale);
    }

    public function render()
    {
        return view('livewire.visitor-registration-success')
            ->title(__('seminar.visitor_registration_success_title').' - '.config('app.name'));
    }
}
