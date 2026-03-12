<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seminar extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'applies_to',
        'amount',
        'original_price',
        'discounted_price',
        'stock_limit',
        'early_bird_deadline',
        'currency',
        'includes_lunch',
        'is_early_bird',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'amount' => 'integer',
        'original_price' => 'integer',
        'discounted_price' => 'integer',
        'stock_limit' => 'integer',
        'early_bird_deadline' => 'datetime',
        'includes_lunch' => 'boolean',
        'is_early_bird' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function seminarRegistrations(): HasMany
    {
        return $this->hasMany(SeminarRegistration::class, 'pricing_tier', 'code');
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->currency === 'USD') {
            return '$'.number_format($this->amount, 2);
        }

        return 'Rp '.number_format($this->amount, 0, ',', '.');
    }

    public function getLabelAttribute(): string
    {
        $parts = [];
        if ($this->is_early_bird) {
            $parts[] = 'Early Bird';
        } else {
            $parts[] = 'Regular';
        }

        if ($this->includes_lunch) {
            $parts[] = 'Snack + Lunch';
        } else {
            $parts[] = 'Snack Only';
        }

        return implode(' - ', $parts);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->applies_to) {
            'local' => 'Local Only',
            'international' => 'International Only',
            'all' => 'All Participants',
            default => $this->applies_to,
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForLocal($query)
    {
        return $query->where(function ($q) {
            $q->where('applies_to', 'local')
                ->orWhere('applies_to', 'all');
        });
    }

    public function scopeForInternational($query)
    {
        return $query->where(function ($q) {
            $q->where('applies_to', 'international')
                ->orWhere('applies_to', 'all');
        });
    }

    public function appliesTo(bool $isIndonesia): bool
    {
        if ($this->applies_to === 'all') {
            return true;
        }

        if ($isIndonesia && $this->applies_to === 'local') {
            return true;
        }

        if (! $isIndonesia && $this->applies_to === 'international') {
            return true;
        }

        return false;
    }

    public function getCurrentPriceAttribute(): int
    {
        if ($this->isEarlyBirdActive() && $this->discounted_price !== null) {
            return $this->discounted_price;
        }

        return $this->original_price ?? $this->amount;
    }

    public function isEarlyBirdActive(): bool
    {
        if ($this->early_bird_deadline === null) {
            return $this->is_early_bird;
        }

        return now()->lt($this->early_bird_deadline);
    }

    public function getRegisteredCount(): int
    {
        return $this->seminarRegistrations()
            ->whereIn('payment_status', ['pending', 'verified'])
            ->count();
    }

    public function getRemainingStockAttribute(): int
    {
        if ($this->stock_limit === null) {
            return PHP_INT_MAX;
        }

        return max(0, $this->stock_limit - $this->getRegisteredCount());
    }

    public function isFull(): bool
    {
        return $this->remaining_stock <= 0;
    }

    public function getFormattedOriginalPriceAttribute(): string
    {
        $price = $this->original_price ?? $this->amount;

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
        $original = $this->original_price ?? $this->amount;
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
