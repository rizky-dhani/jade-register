<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HandsOn extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ho_code',
        'doctor_name',
        'description',
        'flyer_path',
        'skp_path',
        'event_date',
        'max_seats',
        'price',
        'original_price',
        'discounted_price',
        'max_seats',
        'early_bird_deadline',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'event_date' => 'date',
        'max_seats' => 'integer',
        'price' => 'integer',
        'original_price' => 'integer',
        'discounted_price' => 'integer',
        'max_seats' => 'integer',
        'early_bird_deadline' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function handsOnRegistrations(): HasMany
    {
        return $this->hasMany(HandsOnRegistration::class);
    }

    public function seminarRegistrations(): BelongsToMany
    {
        return $this->belongsToMany(SeminarRegistration::class, 'hands_on_registrations')
            ->withPivot(['registration_type', 'payment_status', 'payment_proof_path', 'verified_at'])
            ->withTimestamps();
    }

    public function getRegisteredCount(): int
    {
        return $this->handsOnRegistrations()
            ->whereIn('payment_status', ['pending', 'verified'])
            ->count();
    }

    public function getAvailableSeats(): int
    {
        return max(0, $this->max_seats - $this->getRegisteredCount());
    }

    public function isFull(): bool
    {
        return $this->getAvailableSeats() <= 0;
    }

    public function getCurrentPriceAttribute(): int
    {
        if ($this->isEarlyBirdActive() && $this->discounted_price !== null) {
            return $this->discounted_price;
        }

        return $this->original_price ?? $this->price;
    }

    public function isEarlyBirdActive(): bool
    {
        if ($this->early_bird_deadline === null) {
            return false;
        }

        return now()->lt($this->early_bird_deadline);
    }

    public function getRemainingStockAttribute(): int
    {
        if ($this->max_seats === null) {
            return $this->getAvailableSeats();
        }

        return max(0, $this->max_seats - $this->getRegisteredCount());
    }

    public function getFormattedOriginalPriceAttribute(): string
    {
        $price = $this->original_price ?? $this->price;

        if ($this->currency === 'USD') {
            return '$'.number_format($price, 2);
        }

        return 'Rp '.number_format($price, 0, ',', '.');
    }

    public function getFormattedDiscountedPriceAttribute(): ?string
    {
        if ($this->discounted_price === null) {
            return null;
        }

        if ($this->currency === 'USD') {
            return '$'.number_format($this->discounted_price, 2);
        }

        return 'Rp '.number_format($this->discounted_price, 0, ',', '.');
    }

    public function getSavingsAmountAttribute(): int
    {
        $original = $this->original_price ?? $this->price;
        $discounted = $this->discounted_price ?? $original;

        return max(0, $original - $discounted);
    }

    public function getFormattedSavingsAttribute(): string
    {
        if ($this->currency === 'USD') {
            return '$'.number_format($this->savings_amount, 2);
        }

        return 'Rp '.number_format($this->savings_amount, 0, ',', '.');
    }
}
