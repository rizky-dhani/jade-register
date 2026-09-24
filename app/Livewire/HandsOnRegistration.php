<?php

namespace App\Livewire;

use App\Enums\HandsOnStatus;
use App\Models\Country;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration as HandsOnRegistrationModel;
use App\Models\SeminarRegistration as SeminarRegistrationModel;
use App\Models\Setting;
use App\Services\QrTokenService;
use App\Services\RegistrationService;
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

    public ?HandsOnRegistrationModel $existingHandsOnRegistration = null;

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
            if ($eventId && ! in_array((int) $eventId, $this->alreadyRegisteredHandsOnIds, true)) {
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
        if ($this->hasExistingRegistration()) {
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

        // Someone who already registered hands-on sessions must use the
        // "already registered" flow rather than starting a second registration.
        $existingHandsOnRegistration = HandsOnRegistrationModel::whereRaw('LOWER(email) = ?', [strtolower($this->email)])
            ->whereIn('payment_status', ['pending', 'verified'])
            ->exists();

        if ($existingHandsOnRegistration) {
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
        $codeNumber = substr(HandsOnRegistrationModel::generateRegistrationCode(), -6);

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

        // Determine language based on selected country
        $country = Country::find((int) $this->country_id);
        $language = $country?->is_indonesia ? 'id' : 'en';

        $participantData = [
            'email' => $this->email,
            'name' => $this->name,
            'phone' => $this->phone,
            'country_id' => $this->country_id,
            'language' => $language,
            'payment_method' => $this->payment_method,
            'payment_status' => 'pending',
            'payment_proof_path' => $path,
        ];

        if ($this->is_local) {
            $participantData['name_license'] = $this->name_license;
            $participantData['nik'] = $this->nik;
            $participantData['pdgi_branch'] = $this->pdgi_branch;
            $participantData['kompetensi'] = $this->kompetensi;
        } else {
            $participantData['status'] = $this->status;
        }

        $handsOnRegistrations = [];

        try {
            DB::transaction(function () use ($participantData, &$handsOnRegistrations) {
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

                // Step 2: Seats secured under lock — one standalone registration per session
                foreach ($this->selectedHandsOn as $date => $eventId) {
                    if ($eventId) {
                        $handsOnRegistrations[] = HandsOnRegistrationModel::create($participantData + [
                            'registration_code' => HandsOnRegistrationModel::generateRegistrationCode(),
                            'seminar_registration_id' => null,
                            'hands_on_id' => $eventId,
                            'registration_type' => 'hands_on',
                        ]);
                    }
                }
            });
        } catch (\Exception $e) {
            $this->isSubmitting = false;

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

            throw $e;
        }

        $qrTokenService = app(QrTokenService::class);
        $registrationService = app(RegistrationService::class);
        foreach ($handsOnRegistrations as $hoReg) {
            $qrTokenService->generateForHandsOn($hoReg);
            $registrationService->sendHandsOnSubmissionConfirmation($hoReg);
        }

        $this->redirectRoute('register.hands-on.success', ['id' => $handsOnRegistrations[0]->id, 'type' => 'hands_on'], navigate: true);
    }

    public function submitExistingRegistration(): void
    {
        if ($this->isSubmitting) {
            return;
        }

        $seminarRegistration = $this->existingRegistration;
        $existingPaymentStatus = $seminarRegistration?->payment_status
            ?? $this->existingHandsOnRegistration?->payment_status;

        // Only allow selections for verified registrations
        if ($existingPaymentStatus !== 'verified') {
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
            $codeNumber = substr(HandsOnRegistrationModel::generateRegistrationCode(), -6);
            $extension = $this->payment_proof->getClientOriginalExtension();
            $paymentProofPath = $this->payment_proof->storeAs('payment-proofs', $codeNumber.'.'.$extension, 'public');
        }

        $participantData = $this->existingParticipantData();
        $email = $participantData['email'];

        try {
            DB::transaction(function () use ($seminarRegistration, $participantData, $paymentProofPath, $email) {
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

                    // Skip if already exists in database (extra safety). Matched on
                    // email because standalone rows carry no seminar_registration_id.
                    $alreadyExists = HandsOnRegistrationModel::where('hands_on_id', $eventId)
                        ->whereRaw('LOWER(email) = ?', [strtolower($email)])
                        ->whereIn('payment_status', ['pending', 'verified'])
                        ->exists();

                    if ($alreadyExists) {
                        continue;
                    }

                    HandsOnRegistrationModel::create($participantData + [
                        'registration_code' => HandsOnRegistrationModel::generateRegistrationCode(),
                        'seminar_registration_id' => $seminarRegistration?->id,
                        'hands_on_id' => $eventId,
                        'registration_type' => $seminarRegistration ? 'combined' : 'hands_on',
                        'payment_status' => 'pending',
                        'payment_proof_path' => $paymentProofPath,
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
                'seminar_registration_id' => $seminarRegistration?->id,
                'hands_on_registration_id' => $this->existingHandsOnRegistration?->id,
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', __('seminar.submission_error'));

            return;
        }

        // Generate QR and send confirmation for each new hands-on registration
        $qrTokenService = app(QrTokenService::class);
        $registrationService = app(RegistrationService::class);

        $newRegistrations = HandsOnRegistrationModel::where('payment_status', 'pending')
            ->where('payment_proof_path', $paymentProofPath)
            ->whereRaw('LOWER(email) = ?', [strtolower($email)])
            ->whereIn('hands_on_id', array_values(array_filter(array_map('intval', $this->selectedHandsOn))))
            ->get();

        foreach ($newRegistrations as $hoReg) {
            $qrTokenService->generateForHandsOn($hoReg);
            $registrationService->sendHandsOnSubmissionConfirmation($hoReg);
        }

        $this->isSubmitting = false;

        session()->flash('success', __('seminar.selections_saved'));

        // The success page keys off the id of the record the visitor came from.
        if ($seminarRegistration) {
            $this->redirectRoute('register.hands-on.success', ['id' => $seminarRegistration->id, 'type' => 'seminar'], navigate: true);

            return;
        }

        $this->redirectRoute('register.hands-on.success', [
            'id' => $newRegistrations->first()?->id ?? $this->existingHandsOnRegistration?->id,
            'type' => 'hands_on',
        ], navigate: true);
    }

    public function hasExistingRegistration(): bool
    {
        return $this->existingRegistration !== null || $this->existingHandsOnRegistration !== null;
    }

    /**
     * Participant fields carried onto every new hands-on session, taken from
     * whichever record the visitor verified with.
     *
     * @return array<string, mixed>
     */
    protected function existingParticipantData(): array
    {
        $source = $this->existingRegistration ?? $this->existingHandsOnRegistration;

        return [
            'name' => $source->name,
            'name_license' => $source->name_license,
            'email' => $source->email,
            'phone' => $source->phone,
            'nik' => $source->nik,
            'pdgi_branch' => $source->pdgi_branch,
            'kompetensi' => $source->kompetensi,
            'status' => $source->status,
            'country_id' => $source->country_id,
            'payment_method' => $source->payment_method,
            'language' => $source->language,
        ];
    }

    public function checkExistingRegistration(): void
    {
        $this->validate([
            'verification_email' => 'required|email',
        ]);

        $this->isChecking = true;
        $this->showVerificationError = false;
        $this->existingRegistration = null;
        $this->existingHandsOnRegistration = null;
        $this->alreadyRegisteredHandsOnIds = [];

        $email = strtolower($this->verification_email);

        $registration = SeminarRegistrationModel::with('handsOnRegistrations')
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if ($registration) {
            $this->existingRegistration = $registration;

            $this->alreadyRegisteredHandsOnIds = $registration->handsOnRegistrations
                ->pluck('hands_on_id')
                ->unique()
                ->values()
                ->toArray();
        } else {
            // Standalone hands-on registrants have no seminar registration; find
            // them by email so they can still add sessions.
            $handsOnRegistration = HandsOnRegistrationModel::whereRaw('LOWER(email) = ?', [$email])
                ->whereNull('seminar_registration_id')
                ->whereIn('payment_status', ['pending', 'verified'])
                ->orderByDesc('id')
                ->first();

            if (! $handsOnRegistration) {
                $this->showVerificationError = true;
                $this->isChecking = false;

                return;
            }

            $this->existingHandsOnRegistration = $handsOnRegistration;

            $this->alreadyRegisteredHandsOnIds = HandsOnRegistrationModel::whereRaw('LOWER(email) = ?', [$email])
                ->whereIn('payment_status', ['pending', 'verified'])
                ->pluck('hands_on_id')
                ->unique()
                ->values()
                ->toArray();
        }

        // Pre-populate selectedHandsOn with already-registered events
        $this->selectedHandsOn = [];
        $this->loadAvailableHandsOn();

        $paymentStatus = $this->existingRegistration?->payment_status
            ?? $this->existingHandsOnRegistration?->payment_status;

        if ($paymentStatus === 'verified') {
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
