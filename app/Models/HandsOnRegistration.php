<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HandsOnRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_code',
        'seminar_registration_id',
        'hands_on_id',
        'registration_type',
        'payment_status',
        'payment_proof_path',
        'verified_at',
        'name',
        'name_license',
        'email',
        'phone',
        'nik',
        'pdgi_branch',
        'kompetensi',
        'status',
        'country_id',
        'payment_method',
        'language',
    ];

    public static function generateRegistrationCode(): string
    {
        $prefix = 'JADE-HO-2026-';
        $lastCode = self::where('registration_code', 'like', $prefix.'%')
            ->orderByRaw('CAST(SUBSTRING(registration_code, -6) AS UNSIGNED) DESC')
            ->value('registration_code');

        $nextNumber = 1;
        if ($lastCode) {
            $lastNumber = (int) substr($lastCode, -6);
            $nextNumber = $lastNumber + 1;
        }

        return $prefix.str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    protected $casts = [
        'verified_at' => 'datetime',
        'nik' => 'string',
    ];

    public function seminarRegistration(): BelongsTo
    {
        return $this->belongsTo(SeminarRegistration::class);
    }

    public function handsOn(): BelongsTo
    {
        return $this->belongsTo(HandsOn::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
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
