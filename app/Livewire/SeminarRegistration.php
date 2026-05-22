<?php

namespace App\Livewire;

use App\Enums\HandsOnStatus;
use App\Models\Addon;
use App\Models\AddonRegistration;
use App\Models\Country;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\Seminar;
use App\Models\SeminarRegistration as SeminarRegistrationModel;
use App\Models\Setting;
use App\Services\QrTokenService;
use App\Services\RegistrationService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

    public ?string $payment_proof_path = null;

    public bool $payment_proof_uploaded = false;

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

    public array $alreadyRegisteredHandsOnIds = [];

    // Add-On properties
    public bool $wantsAddons = true;

    public array $selectedAddons = [];

    public array $availableAddons = [];

    public int $addonsTotalPrice = 0;

    // Submission lock to prevent duplicate submissions
    public bool $isSubmitting = false;

    // Existing registration payment proof
    public $existing_payment_proof = null;

    public ?string $existing_payment_proof_path = null;

    public bool $existing_payment_proof_uploaded = false;

    protected function rules(): array
    {
        $paymentProofRule = $this->payment_proof_uploaded
            ? 'nullable'
            : 'required|file|mimes:jpeg,png,pdf|max:5120';

        $existingPaymentProofRule = $this->existing_payment_proof_uploaded
            ? 'nullable'
            : 'required|file|mimes:jpeg,png,pdf|max:5120';

        if (! $this->is_local) {
            return [
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone' => 'required|string|max:20',
                'status' => 'required|string|in:Dentist,Student',
                'country_id' => 'required|integer|min:1|exists:countries,id',
                'selected_seminar' => 'required|string',
                'payment_method' => 'required|string|in:bank_transfer,qris',
                'payment_proof' => $paymentProofRule,
            ];
        }

        $rules = [
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
            'payment_proof' => $paymentProofRule,
            'selectedHandsOn' => 'array',
            'selectedHandsOn.*' => 'nullable|integer|exists:hands_ons,id',
        ];

        // Only require existing_payment_proof when in the existing registration flow
        if ($this->existingRegistration) {
            $rules['existing_payment_proof'] = $existingPaymentProofRule;
        }

        return $rules;
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

        // Load available add-ons
        $this->loadAvailableAddons();
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
            'availableAddons' => $this->availableAddons,
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

    public function updatedSelectedSeminar(): void
    {
        $this->loadAvailableAddons();
    }

    public function updatedPaymentProof(): void
    {
        $this->validateOnly('payment_proof');

        if ($this->payment_proof) {
            $this->payment_proof_uploaded = true;
        }
    }

    public function updatedExistingPaymentProof(): void
    {
        $this->validateOnly('existing_payment_proof');

        if ($this->existing_payment_proof && $this->existingRegistration) {
            $codeNumber = substr($this->existingRegistration->registration_code, -6);
            $extension = $this->existing_payment_proof->getClientOriginalExtension();
            $this->existing_payment_proof_path = $this->existing_payment_proof->storeAs('payment-proofs', $codeNumber.'.'.$extension, 'public');
            $this->existing_payment_proof_uploaded = true;
        }
    }

    public function resetExistingPaymentProof(): void
    {
        $this->existing_payment_proof = null;
        $this->existing_payment_proof_path = null;
        $this->existing_payment_proof_uploaded = false;
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

        // Check if seminar is globally full
        $isSeminarFull = SeminarRegistrationModel::isSeminarFull();

        $packages = Seminar::active()
            ->where(function ($query) use ($isLocal) {
                $query->where('applies_to', $isLocal ? 'local' : 'international')
                    ->orWhere('applies_to', 'all');
            })
            ->orderBy('sort_order')
            ->get();

        return $packages->map(function ($package) use ($isSeminarFull) {
            return [
                'value' => $package->code,
                'label' => $package->name,
                'description' => $package->description,
                'price' => $package->formatted_price,
                'current_price' => $package->current_price,
                'original_price' => $package->formatted_original_price,
                'discounted_price' => $package->formatted_discounted_price,
                'is_early_bird' => $package->isEarlyBirdActive(),
                'savings' => $package->formatted_savings,
                'remaining_stock' => $package->remaining_stock,
                'is_full' => $isSeminarFull || $package->isFull(),
                'includes_lunch' => $package->includes_lunch,
            ];
        })->toArray();
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
            $this->redirectRoute('register.seminar', ['locale' => $this->locale], navigate: true);

            return;
        }

        // Initial check (outside transaction) - quick fail if already full
        if (SeminarRegistrationModel::isSeminarFull()) {
            session()->flash('error', __('seminar.seminar_just_filled'));
            $this->redirectRoute('register.seminar', ['locale' => $this->locale], navigate: true);

            return;
        }

        $this->validate();

        // Prevent duplicate registration by email
        $existingRegistration = SeminarRegistrationModel::whereRaw('LOWER(email) = ?', [strtolower($this->email)])->first();
        if ($existingRegistration) {
            $this->addError('email', __('seminar.email_already_registered'));

            return;
        }

        // Set submission lock
        $this->isSubmitting = true;

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

        $package = Seminar::where('code', $this->selected_seminar)->first();

        if (! $package) {
            $this->addError('selected_seminar', __('seminar.invalid_pricing_tier'));

            return;
        }

        // Generate registration code early for file naming
        $code = SeminarRegistrationModel::generateRegistrationCode();
        $codeNumber = substr($code, -6);

        // Store payment proof with the 6-digit code as filename
        if ($this->payment_proof) {
            $extension = $this->payment_proof->getClientOriginalExtension();
            $path = $this->payment_proof->storeAs('payment-proofs', $codeNumber.'.'.$extension, 'public');
        } elseif ($this->payment_proof_path) {
            // Backward compat: file was stored with old naming (from previous code path)
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

        // Determine language based on selected country (Indonesia = id, others = en)
        $country = Country::find((int) $this->country_id);
        $language = $country?->is_indonesia ? 'id' : 'en';

        $registrationData = [
            'registration_code' => $code,
            'email' => $this->email,
            'name' => $this->name,
            'phone' => $this->phone,
            'country_id' => $this->country_id,
            'language' => $language,
            'registration_type' => 'online',
            'selected_seminar' => $package->name,
            'seminar_id' => $package->id,
            'payment_method' => $this->payment_method,
            'amount' => $package->current_price + $this->handsOnTotalPrice + $this->addonsTotalPrice,
            'currency' => $package->currency,
            'payment_proof_path' => $path,
            'payment_status' => 'pending',
            'wants_hands_on' => $this->wants_hands_on,
            'hands_on_total_amount' => $this->handsOnTotalPrice,
            'addons_total_amount' => $this->addonsTotalPrice,
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

        try {
            $registration = DB::transaction(function () use ($registrationData, $path) {
                // CRITICAL: Check capacity with pessimistic locking INSIDE transaction
                // This prevents race conditions when multiple users submit simultaneously
                if (! SeminarRegistrationModel::checkCapacityAndReserve()) {
                    throw new \Exception(__('seminar.seminar_just_filled'));
                }

                // CRITICAL: Lock HandsOn rows with pessimistic locking BEFORE creating registrations
                // Prevents over-booking when multiple users register for the same hands-on session
                if ($this->wants_hands_on && ! empty($this->selectedHandsOn)) {
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
                }

                $reg = SeminarRegistrationModel::create($registrationData);

                // Create Hands On registrations
                if ($this->wants_hands_on && ! empty($this->selectedHandsOn)) {
                    foreach ($this->selectedHandsOn as $date => $eventId) {
                        if ($eventId) {
                            HandsOnRegistration::create([
                                'seminar_registration_id' => $reg->id,
                                'hands_on_id' => $eventId,
                                'registration_type' => 'combined',
                                'payment_status' => 'pending',
                                'payment_proof_path' => $path,
                            ]);
                        }
                    }
                }

                // Create Addon registrations
                if (! empty($this->selectedAddons)) {
                    foreach ($this->selectedAddons as $addonCode => $selected) {
                        if ($selected) {
                            $addon = Addon::where('code', $addonCode)->first();
                            if ($addon) {
                                AddonRegistration::create([
                                    'seminar_registration_id' => $reg->id,
                                    'addon_id' => $addon->id,
                                    'amount' => $addon->price,
                                    'currency' => $addon->currency,
                                    'payment_proof_path' => $path,
                                    'payment_status' => 'pending',
                                ]);
                            }
                        }
                    }
                }

                return $reg;
            });
        } catch (\Exception $e) {
            // Log the exception for debugging
            \Log::error('Seminar registration failed', [
                'error' => $e->getMessage(),
                'email' => $this->email,
                'trace' => $e->getTraceAsString(),
            ]);

            // Handle hands-on session full race condition (detected inside transaction with lockForUpdate)
            if ($e instanceof \RuntimeException) {
                session()->flash('error', __('seminar.session_full'));
                $this->redirectRoute('register.seminar', ['locale' => $this->locale], navigate: true);

                return;
            }

            // Handle capacity check exception
            if ($e->getMessage() === __('seminar.seminar_just_filled')) {
                session()->flash('error', __('seminar.seminar_just_filled'));
                $this->redirectRoute('register.seminar', ['locale' => $this->locale], navigate: true);

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
        $this->alreadyRegisteredHandsOnIds = [];

        $registration = SeminarRegistrationModel::with('handsOnRegistrations')
            ->whereRaw('LOWER(email) = ?', [strtolower($this->verification_email)])
            ->first();

        if (! $registration) {
            $this->showVerificationError = true;

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

        // Reload addons with purchased status for existing registration
        $this->loadAvailableAddons();

        if ($registration->payment_status === 'verified') {
            $this->loadAvailableHandsOn();

            // Pre-populate selectedHandsOn with already-registered events
            foreach ($this->availableHandsOn as $date => $events) {
                foreach ($events as $event) {
                    if (in_array($event['id'], $this->alreadyRegisteredHandsOnIds)) {
                        $this->selectedHandsOn[$date] = $event['id'];
                        break;
                    }
                }
            }

            $this->updatedSelectedHandsOn();
        }
    }

    public function submitExistingRegistration(): void
    {
        // Prevent duplicate submissions
        if ($this->isSubmitting) {
            return;
        }

        if (! $this->existingRegistration) {
            return;
        }

        $registration = $this->existingRegistration;

        // Only allow for verified registrations
        if ($registration->payment_status !== 'verified') {
            return;
        }

        // Determine if user is trying to add new hands-on selections
        $hasNewHandsOnSelections = false;
        if (! empty($this->selectedHandsOn)) {
            foreach ($this->selectedHandsOn as $eventId) {
                if ($eventId && ! in_array($eventId, $this->alreadyRegisteredHandsOnIds)) {
                    $hasNewHandsOnSelections = true;
                    break;
                }
            }
        }

        // Determine if user is trying to add new addon selections
        $hasNewAddonSelections = false;
        if (! empty($this->selectedAddons)) {
            foreach ($this->selectedAddons as $addonCode => $selected) {
                if ($selected) {
                    $alreadyPurchased = $registration->addonRegistrations()
                        ->whereHas('addon', fn ($q) => $q->where('code', $addonCode))
                        ->where('payment_status', 'verified')
                        ->exists();

                    if (! $alreadyPurchased) {
                        $hasNewAddonSelections = true;
                        break;
                    }
                }
            }
        }

        $hasNewSelections = $hasNewHandsOnSelections || $hasNewAddonSelections;

        // Validate payment proof (required unless already uploaded)
        $this->validate([
            'existing_payment_proof' => $this->existing_payment_proof_uploaded
                ? 'nullable'
                : 'required|file|mimes:jpeg,png,pdf|max:5120',
        ]);

        // Validate hands-on availability for new selections (skip already-registered)
        if ($hasNewHandsOnSelections) {
            foreach ($this->selectedHandsOn as $date => $eventId) {
                if ($eventId && ! in_array($eventId, $this->alreadyRegisteredHandsOnIds)) {
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

        $this->isSubmitting = true;

        $paymentProofPath = $this->existing_payment_proof_path;
        if (! $paymentProofPath && $this->existing_payment_proof) {
            $codeNumber = substr($registration->registration_code, -6);
            $extension = $this->existing_payment_proof->getClientOriginalExtension();
            $paymentProofPath = $this->existing_payment_proof->storeAs('payment-proofs', $codeNumber.'.'.$extension, 'public');
        }

        try {
            DB::transaction(function () use ($registration, $paymentProofPath, $hasNewHandsOnSelections, $hasNewAddonSelections) {
                $hasChanges = false;

                // CRITICAL: Lock HandsOn rows with pessimistic locking for NEW selections
                // Prevents over-booking when multiple users add the same hands-on session
                if ($hasNewHandsOnSelections) {
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
                }

                // Create Hands On registrations for newly selected sessions
                if ($hasNewHandsOnSelections) {
                    foreach ($this->selectedHandsOn as $date => $eventId) {
                        if ($eventId && ! in_array($eventId, $this->alreadyRegisteredHandsOnIds)) {
                            $alreadyRegistered = HandsOnRegistration::where('seminar_registration_id', $registration->id)
                                ->where('hands_on_id', $eventId)
                                ->exists();

                            if (! $alreadyRegistered) {
                                HandsOnRegistration::create([
                                    'seminar_registration_id' => $registration->id,
                                    'hands_on_id' => $eventId,
                                    'registration_type' => 'combined',
                                    'payment_status' => 'pending',
                                    'payment_proof_path' => $paymentProofPath,
                                ]);
                                $hasChanges = true;
                            }
                        }
                    }
                }

                // Create Addon registrations for newly selected add-ons
                if ($hasNewAddonSelections) {
                    foreach ($this->selectedAddons as $addonCode => $selected) {
                        if ($selected) {
                            $addon = Addon::where('code', $addonCode)->first();
                            if ($addon) {
                                $alreadyPurchased = $registration->addonRegistrations()
                                    ->whereHas('addon', fn ($q) => $q->where('code', $addonCode))
                                    ->where('payment_status', 'verified')
                                    ->exists();

                                if (! $alreadyPurchased) {
                                    AddonRegistration::create([
                                        'seminar_registration_id' => $registration->id,
                                        'addon_id' => $addon->id,
                                        'amount' => $addon->price,
                                        'currency' => $addon->currency,
                                        'payment_proof_path' => $paymentProofPath,
                                        'payment_status' => 'pending',
                                    ]);
                                    $hasChanges = true;
                                }
                            }
                        }
                    }
                }

                // Update payment proof on pending HandsOn registrations if a new proof was uploaded
                if ($paymentProofPath) {
                    $updatedCount = $registration->handsOnRegistrations()
                        ->where('payment_status', 'pending')
                        ->whereNull('payment_proof_path')
                        ->update(['payment_proof_path' => $paymentProofPath]);

                    if ($updatedCount > 0) {
                        $hasChanges = true;
                    }
                }

                if (! $hasChanges) {
                    throw new \Exception(__('seminar.no_new_selections'));
                }
            });
        } catch (\Exception $e) {
            $this->isSubmitting = false;

            if ($e->getMessage() === __('seminar.no_new_selections')) {
                $this->addError('existing', __('seminar.no_new_selections'));

                return;
            }

            // Handle hands-on session full detected via lockForUpdate inside transaction
            if ($e instanceof \RuntimeException) {
                session()->flash('error', __('seminar.session_full'));

                return;
            }

            \Log::error('Existing registration submission failed', [
                'error' => $e->getMessage(),
                'registration_id' => $registration->id,
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', __('seminar.submission_error'));

            return;
        }

        $this->isSubmitting = false;

        session()->flash('success', __('seminar.selections_saved'));
        $this->redirectRoute('register.seminar.success', ['id' => $registration->id], navigate: true);
    }

    public static function isRegistrationOpen(): bool
    {
        // Super Admin and Admin bypass the registration toggle
        if (auth()->check() && auth()->user()->hasRole(['Super Admin', 'Admin'])) {
            return true;
        }

        $opensAt = Setting::get('seminar_registration_opens_at');

        if ($opensAt) {
            return now()->gte(Carbon::parse($opensAt));
        }

        return Setting::get('registration_open', true);
    }

    public function loadAvailableHandsOn(): void
    {
        // If registration is manually closed, don't load hands-on
        if (! static::isRegistrationOpen()) {
            $this->availableHandsOn = [];

            return;
        }

        // If seminar is full, don't load hands-on
        if (SeminarRegistrationModel::isSeminarFull()) {
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

    // Add-On methods
    public function loadAvailableAddons(): void
    {
        $selectedPackage = Seminar::where('code', $this->selected_seminar)->first();

        // For already-registered flow, resolve seminar from existing registration
        if (! $selectedPackage && $this->existingRegistration) {
            $selectedPackage = $this->existingRegistration->seminarPackage;
        }

        // Check if seminar is globally full
        $isSeminarFull = SeminarRegistrationModel::isSeminarFull();

        $query = Addon::active()
            ->available()
            ->orderBy('sort_order');

        $this->availableAddons = $query->get()
            ->map(function ($addon) use ($selectedPackage, $isSeminarFull) {
                // Skip if seminar is full
                if ($isSeminarFull) {
                    return null;
                }

                $isPurchased = $this->existingRegistration
                    ? $this->existingRegistration->addonRegistrations()
                        ->whereHas('addon', fn ($q) => $q->where('code', $addon->code))
                        ->where('payment_status', 'verified')
                        ->exists()
                    : false;

                // Pass seminar context to isDisabled for condition evaluation
                $isDisabled = $addon->isDisabled([
                    'seminar' => $selectedPackage,
                ]);

                return [
                    'id' => $addon->id,
                    'code' => $addon->code,
                    'name' => $addon->name,
                    'description' => $addon->description,
                    'price' => $addon->price,
                    'formatted_price' => $addon->formatted_price,
                    'remaining_stock' => $addon->remaining_stock,
                    'is_full' => $addon->isFull(),
                    'is_disabled' => $isDisabled,
                    'is_purchased' => $isPurchased,
                ];
            })
            ->filter()
            ->values()
            ->toArray();

        // Unselect any addons that are now disabled (e.g., Lunch Addon when seminar includes lunch)
        foreach ($this->availableAddons as $addon) {
            if ($addon['is_disabled'] && isset($this->selectedAddons[$addon['code']])) {
                unset($this->selectedAddons[$addon['code']]);
            }
        }
    }

    public function updatedSelectedAddons(): void
    {
        $this->addonsTotalPrice = 0;

        foreach ($this->selectedAddons as $addonCode => $selected) {
            if ($selected) {
                $addon = collect($this->availableAddons)->firstWhere('code', $addonCode);
                if ($addon) {
                    $this->addonsTotalPrice += $addon['price'];
                }
            }
        }
    }

    public function getTotalAmount(): int
    {
        $package = Seminar::where('code', $this->selected_seminar)->first();
        $seminarAmount = $package ? $package->current_price : 0;

        return $seminarAmount + $this->handsOnTotalPrice;
    }
}
