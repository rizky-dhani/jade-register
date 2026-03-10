<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeminarRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_code',
        'email',
        'name',
        'name_license',
        'nik',
        'npa',
        'pdgi_branch',
        'kompetensi',
        'phone',
        'country_id',
        'registration_type',
        'pricing_tier',
        'amount',
        'currency',
        'payment_status',
        'payment_proof_path',
        'rejection_reason',
        'verified_by',
        'verified_at',
        'wants_poster_competition',
        'user_id',
        'status',
        'wants_hands_on',
        'hands_on_total_amount',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'amount' => 'integer',
        'wants_poster_competition' => 'boolean',
        'wants_hands_on' => 'boolean',
        'hands_on_total_amount' => 'integer',
    ];

    public static function generateRegistrationCode(): string
    {
        $prefix = 'JDE-SEM';
        $year = date('Y');
        $random = strtoupper(bin2hex(random_bytes(4)));

        return "{$prefix}-{$year}-{$random}";
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function posterSubmissions(): HasMany
    {
        return $this->hasMany(PosterSubmission::class);
    }

    public function handsOnRegistrations(): HasMany
    {
        return $this->hasMany(HandsOnRegistration::class);
    }

    public function handsOnEvents(): BelongsToMany
    {
        return $this->belongsToMany(HandsOnEvent::class, 'hands_on_registrations')
            ->withPivot(['registration_type', 'payment_status', 'payment_proof_path', 'verified_at'])
            ->withTimestamps();
    }

    public function canRegisterHandsOn(): bool
    {
        return $this->payment_status === 'verified';
    }

    public function getTotalHandsOnAmount(): int
    {
        return $this->handsOnRegistrations()
            ->whereIn('payment_status', ['pending', 'verified'])
            ->with('handsOnEvent')
            ->get()
            ->sum(fn ($reg) => $reg->handsOnEvent->price);
    }

    public function canSubmitPoster(): bool
    {
        return $this->payment_status === 'verified'
            && $this->wants_poster_competition
            && $this->user_id !== null;
    }

    public function getPricingTierLabelAttribute(): string
    {
        return $this->pricing_tier ?? 'N/A';
    }

    public function getFormattedAmountAttribute(): string
    {
        if ($this->currency === 'USD') {
            return '$'.number_format($this->amount, 2);
        }

        return 'Rp '.number_format($this->amount, 0, ',', '.');
    }

    public function getRegistrationTypeLabelAttribute(): string
    {
        return ucfirst($this->registration_type ?? 'Online');
    }
}
