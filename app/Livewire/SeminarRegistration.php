<?php

namespace App\Livewire;

use App\Models\Country;
use App\Models\SeminarRegistration as SeminarRegistrationModel;
use App\Models\User;
use App\Services\RegistrationService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class SeminarRegistration extends Component
{
    use WithFileUploads;

    protected static string $view = 'livewire.seminar-registration';

    public bool $is_local = true;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $name_license = '';

    public string $nik = '';

    public string $pdgi_branch = '';

    public string $kompetensi = '';

    public string $status = '';

    public ?int $country_id = null;

    public ?string $pricing_tier = null;

    public $payment_proof = null;

    public bool $wants_poster_competition = false;

    public string $password = '';

    public string $password_confirmation = '';

    public bool $isSuccess = false;

    public ?SeminarRegistrationModel $registration = null;

    #[Url(as: 'lang', keep: true)]
    public string $locale = 'en';

    protected $queryString = ['locale'];

    // Already registered check properties
    public ?string $is_already_registered = null;

    public string $verification_email = '';

    public ?SeminarRegistrationModel $existingRegistration = null;

    public bool $showVerificationError = false;

    // Hands On properties
    public bool $wants_hands_on = false;

    public array $selectedHandsOn = [];

    public array $availableHandsOn = [];

    public int $handsOnTotalPrice = 0;

    protected function rules(): array
    {
        if (! $this->is_local) {
            return [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:seminar_registrations,email',
                'phone' => 'required|string|max:20',
                'status' => 'required|string|in:Dentist,Student',
                'country_id' => 'required|integer|min:1|exists:countries,id',
                'pricing_tier' => 'required|string',
                'payment_proof' => 'required|file|mimes:jpeg,png,pdf|max:5120',
            ];
        }

        return [
            'email' => 'required|email|unique:seminar_registrations,email',
            'name' => 'required|string|max:255',
            'name_license' => 'required|string|max:255',
            'nik' => 'required|string|max:20',
            'pdgi_branch' => 'required|string|max:255',
            'kompetensi' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'country_id' => 'required|integer|min:1|exists:countries,id',
            'pricing_tier' => 'required|string',
            'payment_proof' => 'required|file|mimes:jpeg,png,pdf|max:5120',
            'password' => 'required_if:wants_poster_competition,true|confirmed|min:8',
            'selectedHandsOn' => 'array',
            'selectedHandsOn.*' => 'nullable|integer|exists:hands_on_events,id',
        ];
    }

    protected $messages = [
        'password.required_if' => 'Password is required if you want to participate in the poster competition.',
        'password.confirmed' => 'Password confirmation does not match.',
    ];

    public function mount(): void
    {
        $this->locale = in_array($this->locale, ['en', 'id']) ? $this->locale : 'en';
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
        $this->locale = in_array($this->locale, ['en', 'id']) ? $this->locale : 'en';
        App::setLocale($this->locale);
    }

    public function updatedIsLocal(): void
    {
        $this->pricing_tier = null;
    }

    public function render()
    {
        $countries = Country::orderBy('name')->get();

        return view('livewire.seminar-registration', [
            'countries' => $countries,
            'availableTiers' => $this->getAvailableTiers(),
            'isIndonesia' => $this->isIndonesia(),
            'availableHandsOn' => $this->availableHandsOn,
        ]);
    }

    public function updatedCountryId(): void
    {
        $this->pricing_tier = null;
        if ($this->country_id) {
            $country = Country::find((int) $this->country_id);
            $this->is_local = $country?->is_indonesia ?? true;
        }
    }

    public function isIndonesia(): bool
    {
        if (! $this->country_id) {
            return true;
        }

        $country = Country::find((int) $this->country_id);

        return $country?->is_indonesia ?? true;
    }

    public function getAvailableTiers(): array
    {
        if (empty($this->country_id)) {
            return [];
        }

        $country = Country::find((int) $this->country_id);
        if (! $country) {
            return [];
        }

        $isLocal = $country->is_indonesia;
        $this->is_local = $isLocal;

        $packages = \App\Models\SeminarPackage::active()
            ->where(function ($query) use ($isLocal) {
                $query->where('applies_to', $isLocal ? 'local' : 'international')
                    ->orWhere('applies_to', 'all');
            })
            ->orderBy('sort_order')
            ->get();

        return $packages->map(function ($package) {
            return [
                'value' => $package->code,
                'label' => $package->label,
                'price' => $package->formatted_price,
            ];
        })->toArray();
    }

    public function submit()
    {
        $this->validate();

        // Validate Hands On selections have available seats
        if ($this->wants_hands_on && ! empty($this->selectedHandsOn)) {
            foreach ($this->selectedHandsOn as $date => $eventId) {
                if ($eventId) {
                    $event = HandsOn::find($eventId);
                    if ($event && $event->getAvailableSeats() <= 0) {
                        $this->addError('selectedHandsOn.'.$date, 'This session is now full. Please select another.');

                        return;
                    }
                }
            }
        }

        $package = \App\Models\Seminar::where('code', $this->pricing_tier)->first();

        if (! $package) {
            $this->addError('pricing_tier', 'Invalid pricing tier selected.');

            return;
        }

        $path = $this->payment_proof->store('payment-proofs', 'public');

        $userId = null;
        if ($this->wants_poster_competition && $this->is_local) {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);
            $user->assignRole('poster-participant');
            $userId = $user->getKey();
        }

        $registrationData = [
            'registration_code' => SeminarRegistrationModel::generateRegistrationCode(),
            'email' => $this->email,
            'name' => $this->name,
            'phone' => $this->phone,
            'country_id' => $this->country_id,
            'registration_type' => 'online',
            'pricing_tier' => $package->name,
            'amount' => $package->amount + $this->handsOnTotalPrice,
            'currency' => $package->currency,
            'payment_proof_path' => $path,
            'payment_status' => 'pending',
            'wants_poster_competition' => $this->is_local ? $this->wants_poster_competition : false,
            'wants_hands_on' => $this->wants_hands_on,
            'hands_on_total_amount' => $this->handsOnTotalPrice,
            'user_id' => $userId,
        ];

        if ($this->is_local) {
            $registrationData['name_license'] = $this->name_license;
            $registrationData['nik'] = $this->nik;
            $registrationData['pdgi_branch'] = $this->pdgi_branch;
            $registrationData['kompetensi'] = $this->kompetensi;
        } else {
            $registrationData['status'] = $this->status;
        }

        $registration = SeminarRegistrationModel::create($registrationData);

        // Create Hands On registrations
        if ($this->wants_hands_on && ! empty($this->selectedHandsOn)) {
            foreach ($this->selectedHandsOn as $date => $eventId) {
                if ($eventId) {
                    HandsOnRegistration::create([
                        'seminar_registration_id' => $registration->id,
                        'hands_on_event_id' => $eventId,
                        'registration_type' => 'combined',
                        'payment_status' => 'pending',
                        'payment_proof_path' => $path,
                    ]);
                }
            }
        }

        $registrationService = app(RegistrationService::class);
        $registrationService->sendSeminarSubmissionConfirmation($registration);

        $this->registration = $registration;
        $this->isSuccess = true;
    }

    public function checkExistingRegistration(): void
    {
        $this->validate([
            'verification_email' => 'required|email',
        ]);

        $this->showVerificationError = false;
        $this->existingRegistration = null;

        $registration = SeminarRegistrationModel::whereRaw('LOWER(email) = ?', [strtolower($this->verification_email)])
            ->first();

        if (! $registration) {
            $this->showVerificationError = true;

            return;
        }

        $this->existingRegistration = $registration;

        if ($registration->payment_status === 'verified') {
            $this->loadAvailableHandsOn();
        }
    }

    public function loadAvailableHandsOn(): void
    {
        $events = HandsOn::where('is_active', true)
            ->whereIn('event_date', ['2026-11-13', '2026-11-14', '2026-11-15'])
            ->orderBy('event_date')
            ->orderBy('name')
            ->get();

        $this->availableHandsOn = [];

        foreach ($events as $event) {
            $date = $event->event_date->format('Y-m-d');
            if (! isset($this->availableHandsOn[$date])) {
                $this->availableHandsOn[$date] = [];
            }

            $registeredCount = $event->handsOnRegistrations()
                ->whereIn('payment_status', ['pending', 'verified'])
                ->count();

            $availableSeats = max(0, $event->max_seats - $registeredCount);

            $this->availableHandsOn[$date][] = [
                'id' => $event->id,
                'name' => $event->name,
                'description' => $event->description,
                'price' => $event->price,
                'max_seats' => $event->max_seats,
                'registered_count' => $registeredCount,
                'available_seats' => $availableSeats,
                'is_full' => $availableSeats <= 0,
            ];
        }
    }

    public function updatedWantsHandsOn(): void
    {
        if ($this->wants_hands_on) {
            $this->loadAvailableHandsOn();
        } else {
            $this->selectedHandsOn = [];
            $this->handsOnTotalPrice = 0;
        }
    }

    public function updatedSelectedHandsOn(): void
    {
        $this->handsOnTotalPrice = 0;

        foreach ($this->selectedHandsOn as $date => $eventId) {
            if ($eventId) {
                foreach ($this->availableHandsOn[$date] ?? [] as $event) {
                    if ($event['id'] == $eventId) {
                        $this->handsOnTotalPrice += $event['price'];
                        break;
                    }
                }
            }
        }
    }

    public function getTotalAmount(): int
    {
        $package = \App\Models\Seminar::where('code', $this->pricing_tier)->first();
        $seminarAmount = $package ? $package->amount : 0;

        return $seminarAmount + $this->handsOnTotalPrice;
    }
}
