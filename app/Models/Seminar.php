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
        'currency',
        'includes_lunch',
        'is_early_bird',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'amount' => 'integer',
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
}
