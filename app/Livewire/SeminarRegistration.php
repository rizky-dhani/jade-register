<?php

namespace App\Livewire;

use App\Models\Country;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration as SeminarRegistrationModel;
use App\Services\QrTokenService;
use App\Services\RegistrationService;
use Illuminate\Support\Facades\App;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class SeminarRegistration extends Component
{
    use WithFileUploads;

    protected static string $view = 'livewire.seminar-registration';

    public bool $is_local = true;

    public ?string $name = null;

    public string $email = '';

    public string $phone = '';

    public string $name_license = '';

    public string $nik = '';

    public string $pdgi_branch = '';

    public string $kompetensi = '';

    public string $status = '';

    public ?int $country_id = null;

    public ?string $selected_seminar = null;

    public string $payment_method = 'bank_transfer';

    public $payment_proof = null;

    public bool $wants_poster_competition = false;

    #[Url(as: 'lang', keep: true)]
    public string $locale = 'id';

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
                'email' => 'required|email',
                'phone' => 'required|string|max:20',
                'status' => 'required|string|in:Dentist,Student',
                'country_id' => 'required|integer|min:1|exists:countries,id',
                'selected_seminar' => 'required|string',
                'payment_method' => 'required|string|in:bank_transfer,qris',
                'payment_proof' => 'required|file|mimes:jpeg,png,pdf|max:5120',
            ];
        }

        return [
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
            'name_license' => 'required|string|max:255',
            'nik' => 'required|string|max:20',
            'pdgi_branch' => 'required|string|max:255',
            'kompetensi' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'country_id' => 'required|integer|min:1|exists:countries,id',
            'selected_seminar' => 'required|string',
            'payment_method' => 'required|string|in:bank_transfer,qris',
            'payment_proof' => 'required|file|mimes:jpeg,png,pdf|max:5120',
            'selectedHandsOn' => 'array',
            'selectedHandsOn.*' => 'nullable|integer|exists:hands_ons,id',
        ];
    }

    public function mount(): void
    {
        $this->locale = in_array($this->locale, ['en', 'id']) ? $this->locale : 'en';
        App::setLocale($this->locale);

        // Set default country to Indonesia
        $this->country_id = 1;

        // Pre-fill data if user is authenticated
        if (auth()->check()) {
            $user = auth()->user();
            $this->email = $user->email;
            $this->name = $user->name ?? '';
            $this->name_license = $user->name_license ?? '';
            $this->nik = $user->nik ?? '';
            $this->pdgi_branch = $user->pdgi_branch ?? '';
            $this->kompetensi = $user->kompetensi ?? '';
            $this->phone = $user->phone ?? '';
            $this->country_id = $user->country_id ?? 1;
        }
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
        $this->selected_seminar = null;
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
        $this->selected_seminar = null;
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

        $packages = \App\Models\Seminar::active()
            ->where(function ($query) use ($isLocal) {
                $query->where('applies_to', $isLocal ? 'local' : 'international')
                    ->orWhere('applies_to', 'all');
            })
            ->orderBy('sort_order')
            ->get();

        return $packages->map(function ($package) {
            return [
                'value' => $package->code,
                'label' => $package->name,
                'price' => $package->formatted_price,
                'current_price' => $package->current_price,
                'original_price' => $package->formatted_original_price,
                'discounted_price' => $package->formatted_discounted_price,
                'is_early_bird' => $package->isEarlyBirdActive(),
                'savings' => $package->formatted_savings,
                'remaining_stock' => $package->remaining_stock,
                'is_full' => $package->isFull(),
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
                    if ($event) {
                        if ($event->isFull()) {
                            $this->addError('selectedHandsOn.'.$date, __('seminar.session_full'));

                            return;
                        }
                        if ($event->remaining_stock <= 0) {
                            $this->addError('selectedHandsOn.'.$date, __('seminar.session_stock_limit'));

                            return;
                        }
                    }
                }
            }
        }

        $package = \App\Models\Seminar::where('code', $this->selected_seminar)->first();

        if (! $package) {
            $this->addError('selected_seminar', __('seminar.invalid_pricing_tier'));

            return;
        }

        $path = $this->payment_proof->store('payment-proofs', 'public');

        $userId = auth()->id() ?: null;

        // Determine language based on selected country (Indonesia = id, others = en)
        $country = Country::find((int) $this->country_id);
        $language = $country?->is_indonesia ? 'id' : 'en';

        $registrationData = [
            'registration_code' => SeminarRegistrationModel::generateRegistrationCode(),
            'email' => $this->email,
            'name' => $this->name,
            'phone' => $this->phone,
            'country_id' => $this->country_id,
            'language' => $language,
            'registration_type' => 'online',
            'selected_seminar' => $package->name,
            'payment_method' => $this->payment_method,
            'amount' => $package->current_price + $this->handsOnTotalPrice,
            'currency' => $package->currency,
            'payment_proof_path' => $path,
            'payment_status' => 'pending',
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

        $qrTokenService = app(QrTokenService::class);
        $qrTokenService->generate($registration);

        // Create Hands On registrations
        if ($this->wants_hands_on && ! empty($this->selectedHandsOn)) {
            foreach ($this->selectedHandsOn as $date => $eventId) {
                if ($eventId) {
                    HandsOnRegistration::create([
                        'seminar_registration_id' => $registration->id,
                        'hands_on_id' => $eventId,
                        'registration_type' => 'combined',
                        'payment_status' => 'pending',
                        'payment_proof_path' => $path,
                    ]);
                }
            }
        }

        $registrationService = app(RegistrationService::class);
        $registrationService->sendSeminarSubmissionConfirmation($registration);

        $this->redirectRoute('register.seminar.success', ['id' => $registration->id], navigate: true);
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

            $this->availableHandsOn[$date][] = [
                'id' => $event->id,
                'name' => $event->name,
                'description' => $event->description,
                'price' => $event->current_price,
                'original_price' => $event->formatted_original_price,
                'discounted_price' => $event->formatted_discounted_price,
                'is_early_bird' => $event->isEarlyBirdActive(),
                'savings' => $event->formatted_savings,
                'max_seats' => $event->max_seats,
                'remaining_stock' => $event->remaining_stock,
                'is_full' => $event->isFull(),
                'has_price' => $event->current_price !== null && $event->current_price > 0,
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
        $package = \App\Models\Seminar::where('code', $this->selected_seminar)->first();
        $seminarAmount = $package ? $package->current_price : 0;

        return $seminarAmount + $this->handsOnTotalPrice;
    }
}
