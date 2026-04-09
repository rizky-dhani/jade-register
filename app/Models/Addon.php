<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Addon extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'price',
        'currency',
        'max_seats',
        'is_active',
        'available_from',
        'available_until',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'integer',
        'max_seats' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'available_from' => 'date',
        'available_until' => 'date',
    ];

    public function addonRegistrations(): HasMany
    {
        return $this->hasMany(AddonRegistration::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('available_from')
                ->orWhere('available_from', '<=', now());
        })->where(function ($q) {
            $q->whereNull('available_until')
                ->orWhere('available_until', '>=', now());
        });
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->currency === 'USD') {
            return '$'.number_format($this->price, 2);
        }

        return 'Rp '.number_format($this->price, 0, ',', '.');
    }

    public function getRemainingStockAttribute(): int
    {
        if ($this->max_seats === null) {
            return PHP_INT_MAX;
        }

        return max(0, $this->max_seats - $this->addonRegistrations()
            ->where('payment_status', 'verified')
            ->count());
    }

    public function isFull(): bool
    {
        return $this->remaining_stock <= 0;
    }
}
