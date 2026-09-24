<?php

namespace App\Livewire;

use App\Models\HandsOnRegistration as HandsOnRegistrationModel;
use App\Models\SeminarRegistration as SeminarRegistrationModel;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class HandsOnRegistrationSuccess extends Component
{
    public HandsOnRegistrationModel|SeminarRegistrationModel|null $registration = null;

    public array $handsOnSessions = [];

    /**
     * Hands-on and seminar registrations use independent id sequences, so the
     * id alone is ambiguous. The `type` query parameter, set by the redirect
     * that sent the visitor here, decides which table to look in.
     */
    public function mount(int $id, ?string $type = null): void
    {
        $this->registration = $this->resolveRegistration($id, $type);

        if (! $this->registration) {
            abort(404);
        }

        // Set locale based on registration's language preference
        $locale = $this->registration->language ?? 'id';
        $locale = in_array($locale, ['en', 'id']) ? $locale : 'id';
        App::setLocale($locale);

        $this->handsOnSessions = $this->resolveHandsOnSessions();
    }

    protected function resolveRegistration(int $id, ?string $type): HandsOnRegistrationModel|SeminarRegistrationModel|null
    {
        if ($type === 'hands_on') {
            return HandsOnRegistrationModel::find($id);
        }

        if ($type === 'seminar') {
            return SeminarRegistrationModel::find($id);
        }

        // Legacy links carried no type: standalone hands-on ids are the newer
        // sequence, so prefer the hands-on row when both exist.
        return HandsOnRegistrationModel::find($id) ?? SeminarRegistrationModel::find($id);
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    protected function resolveHandsOnSessions(): array
    {
        $registrations = $this->registration instanceof HandsOnRegistrationModel
            ? collect([$this->registration])
            : $this->registration->handsOnRegistrations;

        return $registrations
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

    /**
     * Standalone registrations hold their own amount; combined ones use the
     * seminar registration's rolled-up total.
     */
    public function getHandsOnTotalAmountProperty(): int
    {
        if ($this->registration instanceof HandsOnRegistrationModel) {
            return (int) ($this->registration->handsOn?->current_price ?? 0);
        }

        return (int) $this->registration->hands_on_total_amount;
    }

    public function render()
    {
        return view('livewire.hands-on-registration-success')->title(__('seminar.hands_on_success_title').' - '.config('app.name'));
    }
}
