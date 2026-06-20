<?php

namespace App\Livewire;

use App\Enums\HandsOnStatus;
use App\Models\Country;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration as HandsOnRegistrationModel;
use App\Models\Seminar;
use App\Models\SeminarRegistration as SeminarRegistrationModel;
use App\Models\Setting;
use App\Services\QrTokenService;
use App\Services\RegistrationService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class HandsOnRegistration extends Component
{
    use WithFileUploads;

    protected static string $view = 'livewire.hands-on-registration';

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

    public string $payment_method = 'bank_transfer';

    public $payment_proof = null;

    public ?string $payment_proof_path = null;

    public bool $payment_proof_uploaded = false;

    #[Url(as: 'lang', keep: true)]
    public string $locale = 'id';

    protected $queryString = ['locale'];

    // Already registered check properties
    public ?string $is_already_registered = null;

    public string $verification_email = '';

    public ?SeminarRegistrationModel $existingRegistration = null;

    public bool $showVerificationError = false;

    // Hands On properties
    public array $selectedHandsOn = [];

    public array $availableHandsOn = [];

    public int $handsOnTotalPrice = 0;

    public bool $isChecking = false;

    // Track hands-on events already registered via seminar registration
    public array $alreadyRegisteredHandsOnIds = [];

    // Submission lock to prevent duplicate submissions
    public bool $isSubmitting = false;

    protected function rules(): array
    {
        $paymentProofRule = $this->payment_proof_uploaded
            ? 'nullable'
            : 'required|file|mimes:jpeg,png,pdf|max:5120';

        if (! $this->is_local) {
            return [
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone' => 'required|string|max:20',
                'status' => 'required|string|in:Dentist,Student',
                'country_id' => 'required|integer|min:1|exists:countries,id',
                'payment_method' => 'required|string|in:bank_transfer,qris',
                'payment_proof' => $paymentProofRule,
            ];
        }

        return [
            'email' => 'required|email',
            'name_license' => 'required|string|max:255',
            'nik' => 'required|string|max:20',
            'pdgi_branch' => 'required|string|max:255',
            'kompetensi' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'country_id' => 'required|integer|min:1|exists:countries,id',
            'payment_method' => 'required|string|in:bank_transfer,qris',
            'payment_proof' => $paymentProofRule,
            'selectedHandsOn' => 'required|array|min:1',
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

        // Load available hands-on events
        $this->loadAvailableHandsOn();
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
        // No seminar to clear since there's no package selection
    }

    public function render()
    {
        $countries = Country::orderBy('name')->get();

        return view('livewire.hands-on-registration', [
            'countries' => $countries,
            'isIndonesia' => $this->isIndonesia(),
            'availableHandsOn' => $this->availableHandsOn,
        ])->title(__('seminar.hands_on_sessions').' - '.config('app.name'));
    }

    public function updatedCountryId(): void
    {
        if ($this->country_id) {
            $country = Country::find((int) $this->country_id);
            $this->is_local = $country?->is_indonesia ?? true;
        }
    }

    public function updatedPaymentProof(): void
    {
        $this->validateOnly('payment_proof');

        if ($this->payment_proof) {
            $this->payment_proof_uploaded = true;
        }
    }

    public function resetPaymentProof(): void
    {
        if ($this->payment_proof_path) {
            Storage::disk('public')->delete($this->payment_proof_path);
        }

        $this->payment_proof = null;
        $this->payment_proof_path = null;
        $this->payment_proof_uploaded = false;
    }

    public function isIndonesia(): bool
    {
        if (! $this->country_id) {
            return true;
        }

        $country = Country::find((int) $this->country_id);

        return $country?->is_indonesia ?? true;
    }

    public function loadAvailableHandsOn(): void
    {
        // If registration is manually closed, don't load hands-on
        if (! static::isRegistrationOpen()) {
            $this->availableHandsOn = [];

            return;
        }

        $events = HandsOn::where('is_active', true)
            ->where('status', HandsOnStatus::PUBLISHED)
            ->whereDate('event_date', '>=', '2026-11-13')
            ->whereDate('event_date', '<=', '2026-11-15')
            ->orderBy('event_date')
            ->orderBy('ho_code')
            ->get();

        $this->availableHandsOn = [];

        foreach ($events as $event) {
            $date = $event->event_date->format('Y-m-d');
            if (! isset($this->availableHandsOn[$date])) {
                $this->availableHandsOn[$date] = [];
            }

            $this->availableHandsOn[$date][] = [
                'id' => $event->id,
                'ho_code' => $event->ho_code,
                'name' => $event->name,
                'doctor_name' => $event->doctor_name,
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
                'flyer_url' => $event->flyer_path ? Storage::url($event->flyer_path) : null,
                'skp_url' => $event->skp_path ? Storage::url($event->skp_path) : null,
                'is_already_registered' => in_array($event->id, $this->alreadyRegisteredHandsOnIds),
            ];
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

    public function submit()
    {
        // Prevent duplicate submissions
        if ($this->isSubmitting) {
            return;
        }

        // Check if registration is manually closed
        if (! static::isRegistrationOpen()) {
            session()->flash('error', __('seminar.registration_closed'));
            $this->redirectRoute('register.hands-on', ['locale' => $this->locale], navigate: true);

            return;
        }

        // Handle existing registration flow (adding more hands-on sessions)
        if ($this->existingRegistration) {
            $this->submitExistingRegistration();

            return;
        }

        $this->validate();

        // Prevent duplicate registration by email for new registrations
        $existingRegistration = SeminarRegistrationModel::whereRaw('LOWER(email) = ?', [strtolower($this->email)])->first();
        if ($existingRegistration) {
            $this->addError('email', __('seminar.email_already_registered'));

            return;
        }

        // Set submission lock
        $this->isSubmitting = true;

        // Validate Hands On selections have available seats
        foreach ($this->selectedHandsOn as $date => $eventId) {
            if ($eventId) {
                $event = HandsOn::find($eventId);
                if ($event) {
                    if ($event->isFull()) {
                        $this->addError('selectedHandsOn.'.$date, __('seminar.session_full'));
                        $this->isSubmitting = false;

                        return;
                    }
                    if ($event->remaining_stock <= 0) {
                        $this->addError('selectedHandsOn.'.$date, __('seminar.session_stock_limit'));
                        $this->isSubmitting = false;

                        return;
                    }
                }
            }
        }

        // Generate registration code early for file naming
        $code = HandsOnRegistrationModel::generateRegistrationCode();
        $codeNumber = substr($code, -6);

        // Store payment proof with the 6-digit code as filename
        if ($this->payment_proof) {
            $extension = $this->payment_proof->getClientOriginalExtension();
            $path = $this->payment_proof->storeAs('payment-proofs', $codeNumber.'.'.$extension, 'public');
        } elseif ($this->payment_proof_path) {
            // Backward compat: rename existing file
            $extension = pathinfo($this->payment_proof_path, PATHINFO_EXTENSION);
            $newPath = 'payment-proofs/'.$codeNumber.'.'.$extension;
            if (Storage::disk('public')->exists($this->payment_proof_path)) {
                Storage::disk('public')->move($this->payment_proof_path, $newPath);
            }
            $path = $newPath;
        } else {
            $path = null;
        }

        $userId = auth()->id() ?: null;

        // Determine language based on selected country
        $country = Country::find((int) $this->country_id);
        $language = $country?->is_indonesia ? 'id' : 'en';

        $registrationData = [
            'registration_code' => HandsOnRegistrationModel::generateRegistrationCode(),
            'email' => $this->email,
            'name' => $this->name,
            'phone' => $this->phone,
            'country_id' => $this->country_id,
            'language' => $language,
            'registration_type' => 'hands_on',
            'selected_seminar' => __('seminar.hands_on_sessions'),
            'seminar_id' => null,
            'payment_method' => $this->payment_method,
            'amount' => $this->handsOnTotalPrice,
            'currency' => 'IDR',
            'payment_proof_path' => $path,
            'payment_status' => 'pending',
            'wants_hands_on' => true,
            'hands_on_total_amount' => $this->handsOnTotalPrice,
            'addons_total_amount' => 0,
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

        $registration = null;
        $handsOnRegistrations = [];

        try {
            $registration = DB::transaction(function () use ($registrationData, $path, &$handsOnRegistrations) {
                // Step 1: Lock all HandsOn rows and verify capacity FIRST
                foreach ($this->selectedHandsOn as $date => $eventId) {
                    if ($eventId) {
                        $event = HandsOn::where('id', $eventId)->lockForUpdate()->first();

                        if (! $event) {
                            throw new \RuntimeException("Hands-on event not found for {$date}");
                        }

                        if ($event->isFull()) {
                            throw new \RuntimeException("Hands-on session {$date} is full");
                        }
                    }
                }

                // Step 2: Now safe to create — seats secured under lock
                $reg = SeminarRegistrationModel::create($registrationData);

                // Step 3: Create HandsOnRegistration records
                foreach ($this->selectedHandsOn as $date => $eventId) {
                    if ($eventId) {
                        $hoReg = HandsOnRegistrationModel::create([
                            'registration_code' => HandsOnRegistrationModel::generateRegistrationCode(),
                            'seminar_registration_id' => $reg->id,
                            'hands_on_id' => $eventId,
                            'registration_type' => 'combined',
                            'payment_status' => 'pending',
                            'payment_proof_path' => $path,
                            'name' => $reg->name,
                            'name_license' => $reg->name_license,
                            'email' => $reg->email,
                            'phone' => $reg->phone,
                            'nik' => $reg->nik,
                            'pdgi_branch' => $reg->pdgi_branch,
                            'kompetensi' => $reg->kompetensi,
                            'status' => $reg->status,
                            'country_id' => $reg->country_id,
                            'payment_method' => $reg->payment_method,
                            'language' => $reg->language,
                        ]);

                        $handsOnRegistrations[] = $hoReg;
                    }
                }

                return $reg;
            });
        } catch (\Exception $e) {
            \Log::error('Hands On registration failed', [
                'error' => $e->getMessage(),
                'email' => $this->email,
                'trace' => $e->getTraceAsString(),
            ]);

            // Handle race condition when last seat is taken between pre-check and transaction
            if ($e instanceof \RuntimeException) {
                session()->flash('error', __('seminar.session_full'));
                $this->redirectRoute('register.hands-on', ['locale' => $this->locale], navigate: true);

                return;
            }

            // Handle duplicate entry error (MySQL error code 1062)
            if ($e instanceof QueryException && $e->getCode() === '23000') {
                $existingRegistration = SeminarRegistrationModel::whereRaw('LOWER(email) = ?', [strtolower($this->email)])->first();
                if ($existingRegistration) {
                    $this->addError('email', __('seminar.email_already_registered'));
                    $this->redirectRoute('register.seminar.success', ['id' => $existingRegistration->id], navigate: true);

                    return;
                }
            }

            throw $e;
        }

        $qrTokenService = app(QrTokenService::class);
        $qrTokenService->generate($registration);

        $registrationService = app(RegistrationService::class);
        foreach ($handsOnRegistrations as $hoReg) {
            $qrTokenService->generateForHandsOn($hoReg);
            $registrationService->sendHandsOnSubmissionConfirmation($hoReg);
        }

        $this->redirectRoute('register.hands-on.success', ['id' => $registration->id], navigate: true);
    }

    public function submitExistingRegistration(): void
    {
        if ($this->isSubmitting) {
            return;
        }

        $registration = $this->existingRegistration;

        // Only allow selections for verified registrations
        if ($registration->payment_status !== 'verified') {
            session()->flash('error', __('seminar.complete_payment_first'));

            return;
        }

        $this->isSubmitting = true;

        // Validate hands-on availability and payment proof
        if (! empty($this->selectedHandsOn)) {
            foreach ($this->selectedHandsOn as $date => $eventId) {
                if ($eventId) {
                    // Skip validation for already-registered sessions
                    if (in_array($eventId, $this->alreadyRegisteredHandsOnIds)) {
                        continue;
                    }

                    $event = HandsOn::find($eventId);
                    if ($event) {
                        if ($event->isFull()) {
                            $this->addError('selectedHandsOn.'.$date, __('seminar.session_full'));
                            $this->isSubmitting = false;

                            return;
                        }
                        if ($event->remaining_stock <= 0) {
                            $this->addError('selectedHandsOn.'.$date, __('seminar.session_stock_limit'));
                            $this->isSubmitting = false;

                            return;
                        }
                    }
                }
            }
        }

        $this->validate([
            'payment_proof' => $this->payment_proof_uploaded
                ? 'nullable'
                : 'required|file|mimes:jpeg,png,pdf|max:5120',
        ]);

        $paymentProofPath = $this->payment_proof_path;
        if (! $paymentProofPath && $this->payment_proof) {
            $codeNumber = substr($registration->registration_code, -6);
            $extension = $this->payment_proof->getClientOriginalExtension();
            $paymentProofPath = $this->payment_proof->storeAs('payment-proofs', $codeNumber.'.'.$extension, 'public');
        }

        try {
            DB::transaction(function () use ($registration, $paymentProofPath) {
                $hasNewSelections = false;

                // CRITICAL: Lock HandsOn rows with pessimistic locking for NEW selections
                // Prevents over-booking when multiple users add the same hands-on session
                foreach ($this->selectedHandsOn as $date => $eventId) {
                    if ($eventId && ! in_array($eventId, $this->alreadyRegisteredHandsOnIds)) {
                        $event = HandsOn::where('id', $eventId)->lockForUpdate()->first();
                        if (! $event) {
                            throw new \RuntimeException("Hands-on event not found for {$date}");
                        }
                        if ($event->isFull()) {
                            throw new \RuntimeException("Hands-on session {$date} is full");
                        }
                    }
                }

                foreach ($this->selectedHandsOn as $date => $eventId) {
                    if (! $eventId) {
                        continue;
                    }

                    // Skip if already registered for this session
                    if (in_array($eventId, $this->alreadyRegisteredHandsOnIds)) {
                        continue;
                    }

                    // Skip if already exists in database (extra safety)
                    $alreadyExists = HandsOnRegistrationModel::where('seminar_registration_id', $registration->id)
                        ->where('hands_on_id', $eventId)
                        ->exists();

                    if ($alreadyExists) {
                        continue;
                    }

                    HandsOnRegistrationModel::create([
                        'registration_code' => HandsOnRegistrationModel::generateRegistrationCode(),
                        'seminar_registration_id' => $registration->id,
                        'hands_on_id' => $eventId,
                        'registration_type' => 'combined',
                        'payment_status' => 'pending',
                        'payment_proof_path' => $paymentProofPath,
                        'name' => $registration->name,
                        'name_license' => $registration->name_license,
                        'email' => $registration->email,
                        'phone' => $registration->phone,
                        'nik' => $registration->nik,
                        'pdgi_branch' => $registration->pdgi_branch,
                        'kompetensi' => $registration->kompetensi,
                        'status' => $registration->status,
                        'country_id' => $registration->country_id,
                        'payment_method' => $registration->payment_method,
                        'language' => $registration->language,
                    ]);

                    $hasNewSelections = true;
                }

                if (! $hasNewSelections) {
                    throw new \RuntimeException(__('seminar.no_new_selections'));
                }
            });
        } catch (\Exception $e) {
            $this->isSubmitting = false;

            if ($e->getMessage() === __('seminar.no_new_selections')) {
                $this->addError('selectedHandsOn', __('seminar.no_new_selections'));

                return;
            }

            // Handle hands-on session full detected via lockForUpdate inside transaction
            if ($e instanceof \RuntimeException) {
                session()->flash('error', __('seminar.session_full'));

                return;
            }

            \Log::error('Existing hands-on registration submission failed', [
                'error' => $e->getMessage(),
                'registration_id' => $registration->id,
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', __('seminar.submission_error'));

            return;
        }

        // Generate QR and send confirmation for each new hands-on registration
        $qrTokenService = app(QrTokenService::class);
        $registrationService = app(RegistrationService::class);

        $newRegistrations = HandsOnRegistrationModel::where('seminar_registration_id', $registration->id)
            ->where('payment_status', 'pending')
            ->where('payment_proof_path', $paymentProofPath)
            ->get();

        foreach ($newRegistrations as $hoReg) {
            $qrTokenService->generateForHandsOn($hoReg);
            $registrationService->sendHandsOnSubmissionConfirmation($hoReg);
        }

        $this->isSubmitting = false;

        session()->flash('success', __('seminar.selections_saved'));
        $this->redirectRoute('register.hands-on.success', ['id' => $registration->id], navigate: true);
    }

    public function checkExistingRegistration(): void
    {
        $this->validate([
            'verification_email' => 'required|email',
        ]);

        $this->isChecking = true;
        $this->showVerificationError = false;
        $this->existingRegistration = null;
        $this->alreadyRegisteredHandsOnIds = [];

        $registration = SeminarRegistrationModel::with('handsOnRegistrations')->whereRaw('LOWER(email) = ?', [strtolower($this->verification_email)])
            ->first();

        if (! $registration) {
            $this->showVerificationError = true;
            $this->isChecking = false;

            return;
        }

        $this->existingRegistration = $registration;

        // Load existing hands-on registrations linked to this seminar registration
        $existingHandsOnRegs = $registration->handsOnRegistrations;

        // Track which hands-on event IDs are already registered
        $this->alreadyRegisteredHandsOnIds = $existingHandsOnRegs
            ->pluck('hands_on_id')
            ->unique()
            ->values()
            ->toArray();

        // Pre-populate selectedHandsOn with already-registered events
        $this->selectedHandsOn = [];
        $this->loadAvailableHandsOn();

        if ($registration->payment_status === 'verified') {
            foreach ($this->availableHandsOn as $date => $events) {
                foreach ($events as $event) {
                    if (in_array($event['id'], $this->alreadyRegisteredHandsOnIds)) {
                        $this->selectedHandsOn[$date] = $event['id'];
                        break;
                    }
                }
            }
        }

        $this->updatedSelectedHandsOn();
        $this->isChecking = false;
    }

    public static function isRegistrationOpen(): bool
    {
        // Super Admin and Admin bypass the registration toggle
        if (auth()->check() && auth()->user()->hasRole(['Super Admin', 'Admin'])) {
            return true;
        }

        $opensAt = Setting::get('hands_on_registration_opens_at');

        if ($opensAt && now()->lt($opensAt)) {
            return false;
        }

        $closeAt = Setting::get('hands_on_registration_close_at');

        if ($closeAt && now()->gte($closeAt)) {
            return false;
        }

        return Setting::get('hands_on_registration_open', true);
    }
}
