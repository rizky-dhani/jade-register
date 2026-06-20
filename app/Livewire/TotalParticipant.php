<?php

namespace App\Livewire;

use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\Seminar;
use App\Models\SeminarRegistration;
use Livewire\Component;

class TotalParticipant extends Component
{
    public int $totalSeminar = 0;

    public int $totalHandsOn = 0;

    public array $seminarPackages = [];

    public array $handsOnSessions = [];

    public function mount(): void
    {
        $this->totalSeminar = SeminarRegistration::count();
        $this->totalHandsOn = HandsOnRegistration::count();

        $this->seminarPackages = Seminar::withCount('seminarRegistrations')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Seminar $s) => [
                'name' => $s->name,
                'code' => $s->code,
                'count' => $s->seminar_registrations_count,
                'max_seats' => $s->max_seats,
            ])
            ->toArray();

        $this->handsOnSessions = HandsOn::withCount('handsOnRegistrations')
            ->where('status', 'published')
            ->orderBy('event_date')
            ->get()
            ->map(fn (HandsOn $h) => [
                'name' => $h->name,
                'ho_code' => $h->ho_code,
                'doctor_name' => $h->doctor_name,
                'event_date' => $h->event_date?->format('d M Y'),
                'count' => $h->hands_on_registrations_count,
                'max_seats' => $h->max_seats,
                'price' => $h->price,
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.total-participant');
    }
}
