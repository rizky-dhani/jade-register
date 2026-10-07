<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalWorkshopRegistrationIntent extends Model
{
    use HasFactory;

    protected $fillable = [
        'digital_workshop_id',
        'status',
        'email',
        'name',
        'name_license',
        'nik',
        'pdgi_branch',
        'phone',
        'kompetensi',
        'status_participant',
        'country_id',
        'payment_method',
        'payment_proof_path',
        'language',
        'seminar_registration_id',
        'digital_workshop_registration_id',
        'fulfilled_at',
        'expired_at',
    ];

    protected $casts = [
        'fulfilled_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    /**
     * Normalise the email on write so the observer's lookup and the scope below
     * cannot miss a row that was stored with different casing.
     */
    public function setEmailAttribute(?string $value): void
    {
        $this->attributes['email'] = $value === null ? null : strtolower($value);
    }

    public function digitalWorkshop(): BelongsTo
    {
        return $this->belongsTo(DigitalWorkshop::class);
    }

    public function seminarRegistration(): BelongsTo
    {
        return $this->belongsTo(SeminarRegistration::class);
    }

    public function digitalWorkshopRegistration(): BelongsTo
    {
        return $this->belongsTo(DigitalWorkshopRegistration::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function scopeAwaitingForEmail(Builder $query, string $email): Builder
    {
        return $query
            ->where('status', 'awaiting_seminar')
            ->whereRaw('LOWER(email) = ?', [strtolower($email)]);
    }

    public function isAwaiting(): bool
    {
        return $this->status === 'awaiting_seminar';
    }

    public function isFulfilled(): bool
    {
        return $this->status === 'fulfilled';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    /**
     * Local registrants carry name_license; international ones carry name.
     */
    public function participantName(): ?string
    {
        return $this->name_license ?: $this->name;
    }
}
