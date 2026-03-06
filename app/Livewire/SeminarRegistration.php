<?php

namespace App\Livewire;

use App\Models\Country;
use App\Models\SeminarRegistration as SeminarRegistrationModel;
use Livewire\Component;
use Livewire\WithFileUploads;

class SeminarRegistration extends Component
{
    use WithFileUploads;

    protected static string $view = 'livewire.seminar-registration';

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $name_license = '';

    public string $nik = '';

    public string $npa = '';

    public string $pdgi_branch = '';

    public ?int $country_id = null;

    public ?string $pricing_tier = null;

    public $payment_proof = null;

    public bool $isSuccess = false;

    public ?SeminarRegistrationModel $registration = null;

    protected $rules = [
        'email' => 'required|email|unique:seminar_registrations,email',
        'name' => 'required|string|max:255',
        'name_license' => 'required|string|max:255',
        'nik' => 'required|string|max:20',
        'npa' => 'required|string|max:20',
        'pdgi_branch' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'country_id' => 'required|integer|min:1|exists:countries,id',
        'pricing_tier' => 'required|string',
        'payment_proof' => 'required|file|mimes:jpeg,png,pdf|max:5120',
    ];

    public function render()
    {
        $countries = Country::orderBy('name')->get();

        return view('livewire.seminar-registration', [
            'countries' => $countries,
            'availableTiers' => $this->getAvailableTiers(),
        ]);
    }

    public function updatedCountryId(): void
    {
        $this->pricing_tier = null;
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

        $tiers = [];

        if ($country->is_indonesia) {
            $tiers[] = ['value' => 'early_bird_snack', 'label' => 'Early Bird - Snack Only', 'price' => 'Rp 600.000'];
            $tiers[] = ['value' => 'early_bird_lunch', 'label' => 'Early Bird - Snack + Lunch', 'price' => 'Rp 900.000'];
            $tiers[] = ['value' => 'regular_snack', 'label' => 'Regular - Snack Only', 'price' => 'Rp 900.000'];
            $tiers[] = ['value' => 'regular_lunch', 'label' => 'Regular - Snack + Lunch', 'price' => 'Rp 1.200.000'];
        } else {
            $tiers[] = ['value' => 'intl_early_bird', 'label' => 'Early Bird', 'price' => 'TBA'];
            $tiers[] = ['value' => 'intl_regular', 'label' => 'Regular', 'price' => 'TBA'];
        }

        return $tiers;
    }

    public function submit()
    {
        $this->validate();

        $pricing = match ($this->pricing_tier) {
            'early_bird_snack' => ['amount' => 600000, 'currency' => 'IDR', 'tier' => 'Early Bird - Snack Only'],
            'early_bird_lunch' => ['amount' => 900000, 'currency' => 'IDR', 'tier' => 'Early Bird - Snack + Lunch'],
            'regular_snack' => ['amount' => 900000, 'currency' => 'IDR', 'tier' => 'Regular - Snack Only'],
            'regular_lunch' => ['amount' => 1200000, 'currency' => 'IDR', 'tier' => 'Regular - Snack + Lunch'],
            'intl_early_bird' => ['amount' => 0, 'currency' => 'USD', 'tier' => 'International Early Bird - TBA'],
            'intl_regular' => ['amount' => 0, 'currency' => 'USD', 'tier' => 'International Regular - TBA'],
            default => ['amount' => 0, 'currency' => 'IDR', 'tier' => 'Unknown'],
        };

        $path = $this->payment_proof->store('payment-proofs', 'public');

        $registration = SeminarRegistrationModel::create([
            'registration_code' => SeminarRegistrationModel::generateRegistrationCode(),
            'email' => $this->email,
            'name' => $this->name,
            'name_license' => $this->name_license,
            'nik' => $this->nik,
            'npa' => $this->npa,
            'pdgi_branch' => $this->pdgi_branch,
            'phone' => $this->phone,
            'country_id' => $this->country_id,
            'registration_type' => 'online',
            'pricing_tier' => $pricing['tier'],
            'amount' => $pricing['amount'],
            'currency' => $pricing['currency'],
            'payment_proof_path' => $path,
            'payment_status' => 'pending',
        ]);

        $this->registration = $registration;
        $this->isSuccess = true;
    }
}
