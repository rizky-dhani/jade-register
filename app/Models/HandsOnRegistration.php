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
        'hands_on_id',
        'registration_type',
        'payment_status',
        'payment_proof_path',
        'verified_at',
    ];

    public static function generateRegistrationCode(): string
    {
        return SeminarRegistration::generateRegistrationCode('JADE-HO-2026-');
    }

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function seminarRegistration(): BelongsTo
    {
        return $this->belongsTo(SeminarRegistration::class);
    }

    public function handsOn(): BelongsTo
    {
        return $this->belongsTo(HandsOn::class);
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
