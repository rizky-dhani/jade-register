<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeminarPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_local',
        'amount',
        'currency',
        'includes_lunch',
        'is_early_bird',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_local' => 'boolean',
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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForLocal($query)
    {
        return $query->where('is_local', true);
    }

    public function scopeForInternational($query)
    {
        return $query->where('is_local', false);
    }
}
