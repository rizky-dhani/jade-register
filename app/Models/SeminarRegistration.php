<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\PricingTier;
use App\Enums\RegistrationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeminarRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_code',
        'name',
        'email',
        'phone',
        'affiliation',
        'country_id',
        'registration_type',
        'pricing_tier',
        'amount',
        'payment_status',
        'payment_proof_path',
        'rejection_reason',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'verified_at' => 'datetime',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'IDR '.number_format($this->amount, 0, ',', '.');
    }

    public function getStatusBadgeAttribute(): string
    {
        return PaymentStatus::from($this->payment_status)->getColor();
    }

    public function getRegistrationTypeLabelAttribute(): string
    {
        return RegistrationType::from($this->registration_type)->getLabel();
    }

    public function getPricingTierLabelAttribute(): string
    {
        return PricingTier::from($this->pricing_tier)->getLabel();
    }

    public function isOnline(): bool
    {
        return $this->registration_type === RegistrationType::ONLINE->value;
    }

    public function isOffline(): bool
    {
        return $this->registration_type === RegistrationType::OFFLINE->value;
    }

    public static function findByCode(string $code): ?self
    {
        return static::where('registration_code', $code)->first();
    }

    public static function generateUniqueCode(): string
    {
        $year = date('Y');
        $prefix = "SEM-{$year}-";

        $lastRegistration = static::where('registration_code', 'like', "{$prefix}%")
            ->orderBy('registration_code', 'desc')
            ->first();

        if ($lastRegistration) {
            $lastNumber = (int) substr($lastRegistration->registration_code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix.str_pad((string) $newNumber, 4, '0', STR_PAD_LEFT);
    }
}
