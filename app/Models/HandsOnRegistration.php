<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HandsOnRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'seminar_registration_id',
        'hands_on_event_id',
        'registration_type',
        'payment_status',
        'payment_proof_path',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function seminarRegistration(): BelongsTo
    {
        return $this->belongsTo(SeminarRegistration::class);
    }

    public function handsOnEvent(): BelongsTo
    {
        return $this->belongsTo(HandsOnEvent::class);
    }

    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    public function isVerified(): bool
    {
        return $this->payment_status === 'verified';
    }

    public function isRejected(): bool
    {
        return $this->payment_status === 'rejected';
    }
}
