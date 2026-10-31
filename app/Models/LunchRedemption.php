<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LunchRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'seminar_registration_id',
        'redeemed_at',
        'redeemed_by',
        'redeemed_by_name',
    ];

    protected $casts = [
        'redeemed_at' => 'datetime',
    ];

    public function seminarRegistration(): BelongsTo
    {
        return $this->belongsTo(SeminarRegistration::class);
    }

    public function redeemedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'redeemed_by');
    }
}
