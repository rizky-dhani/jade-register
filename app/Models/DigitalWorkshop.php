<?php

namespace App\Models;

use App\Enums\HandsOnStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DigitalWorkshop extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'event_date',
        'event_time',
        'event_end_time',
        'location',
        'price',
        'bundle_price',
        'max_seats',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'price' => 'integer',
            'bundle_price' => 'integer',
            'max_seats' => 'integer',
            'sort_order' => 'integer',
            'status' => HandsOnStatus::class,
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', HandsOnStatus::PUBLISHED);
    }

    public function getRegisteredCount(): int
    {
        // The registrations relation arrives with the registrations plan.
        // Do not assert a real count until that table exists.
        return 0;
    }

    public function getAvailableSeats(): int
    {
        if ($this->max_seats === null) {
            return PHP_INT_MAX;
        }

        return max(0, $this->max_seats - $this->getRegisteredCount());
    }

    public function isFull(): bool
    {
        return $this->getAvailableSeats() <= 0;
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp '.number_format($this->price ?? 0, 0, ',', '.');
    }

    public function getFormattedBundlePriceAttribute(): string
    {
        return 'Rp '.number_format($this->bundle_price ?? 0, 0, ',', '.');
    }
}
