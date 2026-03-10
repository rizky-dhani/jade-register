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
        'description',
        'event_date',
        'max_seats',
        'price',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'event_date' => 'date',
        'max_seats' => 'integer',
        'price' => 'integer',
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
}
