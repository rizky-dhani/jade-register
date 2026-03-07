<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'amount' => 'integer',
        'wants_poster_competition' => 'boolean',
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

    public function posterSubmissions(): HasMany
    {
        return $this->hasMany(PosterSubmission::class);
    }

    public function canSubmitPoster(): bool
    {
        return $this->payment_status === 'verified'
            && $this->wants_poster_competition
            && $this->user_id !== null;
    }
}
