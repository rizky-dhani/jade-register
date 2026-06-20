<?php

namespace App\Livewire;

use App\Models\HandsOnRegistration as HandsOnRegistrationModel;
use App\Models\SeminarRegistration as SeminarRegistrationModel;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class HandsOnRegistrationSuccess extends Component
{
    public ?SeminarRegistrationModel $registration = null;

    public array $handsOnSessions = [];

    public function mount(int $id): void
    {
        $this->registration = SeminarRegistrationModel::with('handsOnRegistrations.handsOn')->find($id);

        if (! $this->registration) {
            abort(404);
        }

        // Set locale based on registration's language preference
        $locale = $this->registration->language ?? 'id';
        $locale = in_array($locale, ['en', 'id']) ? $locale : 'id';
        App::setLocale($locale);

        // Load Hands-On sessions
        $this->handsOnSessions = $this->registration->handsOnRegistrations
            ->filter(fn (HandsOnRegistrationModel $reg) => $reg->handsOn)
            ->map(fn (HandsOnRegistrationModel $reg) => [
                'code' => $reg->handsOn->ho_code,
                'name' => $reg->handsOn->name,
                'doctor' => $reg->handsOn->doctor_name,
                'date' => $reg->handsOn->event_date?->format('d M Y'),
                'flyer_url' => $reg->handsOn->flyer_path ? Storage::url($reg->handsOn->flyer_path) : null,
            ])
            ->values()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.hands-on-registration-success')->title(__('seminar.hands_on_success_title').' - '.config('app.name'));
    }
}
