<?php

namespace App\Livewire;

use App\Enums\HandsOnStatus;
use App\Jobs\CompleteDigitalWorkshopRegistration;
use App\Models\Country;
use App\Models\DigitalWorkshop;
use App\Models\DigitalWorkshopRegistration as DigitalWorkshopRegistrationModel;
use App\Services\DigitalWorkshopPricingService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class DigitalWorkshopRegistration extends Component
{
    use WithFileUploads;

    protected static string $view = 'livewire.digital-workshop-registration';

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

    public bool $isSubmitting = false;

    public ?DigitalWorkshop $digitalWorkshop = null;

    /** @var array{amount: int, registration_type: string, seminar_registration_id: int|null} */
    public array $priceBreakdown = [
        'amount' => 0,
        'registration_type' => 'standalone',
        'seminar_registration_id' => null,
    ];

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
        ];
    }

    public function mount(): void
    {
        $this->locale = in_array($this->locale, ['en', 'id']) ? $this->locale : 'id';
        App::setLocale($this->locale);

        $this->country_id = 1;

        $this->digitalWorkshop = DigitalWorkshop::query()
            ->where('status', HandsOnStatus::PUBLISHED)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        if (! $this->digitalWorkshop) {
            abort(404);
        }

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

        $this->refreshPriceBreakdown();
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

    public function refreshPriceBreakdown(): void
    {
        if (! $this->digitalWorkshop || ! filled($this->email)) {
            $this->priceBreakdown = [
                'amount' => (int) ($this->digitalWorkshop?->price ?? 0),
                'registration_type' => 'standalone',
                'seminar_registration_id' => null,
            ];

            return;
        }

        $this->priceBreakdown = app(DigitalWorkshopPricingService::class)
            ->forEmail($this->email, $this->digitalWorkshop);
    }

    public function updatedEmail(): void
    {
        $this->refreshPriceBreakdown();
    }

    public function submit()
    {
        if ($this->isSubmitting) {
            return;
        }

        $workshop = $this->digitalWorkshop;

        if (! $workshop) {
            abort(404);
        }

        if ($workshop->isFull()) {
            $this->addError('email', __('seminar.digital_workshop_session_full'));

            return;
        }

        $duplicate = DigitalWorkshopRegistrationModel::whereRaw('LOWER(email) = ?', [strtolower($this->email)])
            ->where('digital_workshop_id', $workshop->id)
            ->whereIn('payment_status', ['pending', 'verified'])
            ->exists();

        if ($duplicate) {
            $this->addError('email', __('seminar.email_already_registered'));

            return;
        }

        $this->validate();

        $this->isSubmitting = true;

        $pricing = app(DigitalWorkshopPricingService::class)->forEmail($this->email, $workshop);

        $codeNumber = substr(DigitalWorkshopRegistrationModel::generateRegistrationCode(), -6);

        $path = null;
        if ($this->payment_proof) {
            $extension = $this->payment_proof->getClientOriginalExtension();
            $path = $this->payment_proof->storeAs('payment-proofs', $codeNumber.'.'.$extension, 'public');
        }

        $participantData = [
            'email' => $this->email,
            'phone' => $this->phone,
            'country_id' => $this->country_id,
            'language' => $this->locale,
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
            $participantData['name'] = $this->name;
            $participantData['status'] = $this->status;
        }

        try {
            $registration = DB::transaction(function () use ($workshop, $pricing, $participantData) {
                // Re-check capacity under lock: the pre-check above is not atomic.
                $locked = DigitalWorkshop::where('id', $workshop->id)->lockForUpdate()->first();

                if (! $locked || $locked->isFull()) {
                    throw new \RuntimeException('Digital workshop is full');
                }

                return DigitalWorkshopRegistrationModel::create($participantData + [
                    'registration_code' => DigitalWorkshopRegistrationModel::generateRegistrationCode(),
                    'digital_workshop_id' => $workshop->id,
                    'seminar_registration_id' => $pricing['seminar_registration_id'],
                    'registration_type' => $pricing['registration_type'],
                    'amount' => $pricing['amount'],
                ]);
            });
        } catch (\Exception $e) {
            $this->isSubmitting = false;

            \Log::error('Digital Workshop registration failed', [
                'error' => $e->getMessage(),
                'email' => $this->email,
                'trace' => $e->getTraceAsString(),
            ]);

            if ($e instanceof \RuntimeException) {
                session()->flash('error', __('seminar.digital_workshop_session_full'));
                $this->redirectRoute('register.digital-workshop', ['locale' => $this->locale], navigate: true);

                return;
            }

            throw $e;
        }

        CompleteDigitalWorkshopRegistration::dispatch($registration);

        $this->redirectRoute('register.digital-workshop.success', ['id' => $registration->id], navigate: true);
    }

    public function isIndonesia(): bool
    {
        return (bool) Country::find((int) $this->country_id)?->is_indonesia;
    }

    public function render()
    {
        return view('livewire.digital-workshop-registration', [
            'countries' => Country::orderBy('name')->get(),
        ])->title(__('seminar.digital_workshop_title').' - '.config('app.name'));
    }
}
