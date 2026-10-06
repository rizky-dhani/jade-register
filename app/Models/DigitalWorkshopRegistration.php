<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalWorkshopRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_code',
        'qr_token',
        'qr_expires_at',
        'digital_workshop_id',
        'seminar_registration_id',
        'registration_type',
        'amount',
        'email',
        'name',
        'name_license',
        'nik',
        'pdgi_branch',
        'phone',
        'kompetensi',
        'status',
        'country_id',
        'payment_status',
        'payment_method',
        'payment_proof_path',
        'rejection_reason',
        'verified_at',
        'confirmation_email_sent_at',
        'language',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'nik' => 'string',
            'verified_at' => 'datetime',
            'confirmation_email_sent_at' => 'datetime',
            'qr_expires_at' => 'datetime',
        ];
    }

    public function digitalWorkshop(): BelongsTo
    {
        return $this->belongsTo(DigitalWorkshop::class);
    }

    public function seminarRegistration(): BelongsTo
    {
        return $this->belongsTo(SeminarRegistration::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public static function generateRegistrationCode(): string
    {
        $prefix = 'JADE-DW-2026-';
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

    public function isBundled(): bool
    {
        return $this->seminar_registration_id !== null;
    }

    public function recipientEmail(): ?string
    {
        $email = $this->email ?: $this->seminarRegistration?->email;

        return filled($email) ? (string) $email : null;
    }

    public function recipientLanguage(): string
    {
        $language = $this->language ?: $this->seminarRegistration?->language;

        return filled($language) ? (string) $language : 'en';
    }
}
