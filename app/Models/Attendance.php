<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'seminar_registration_id',
        'hands_on_registration_id',
        'activity_type',
        'checked_in_at',
        'checked_in_by',
        'notes',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
    ];

    public function seminarRegistration(): BelongsTo
    {
        return $this->belongsTo(SeminarRegistration::class);
    }

    public function handsOnRegistration(): BelongsTo
    {
        return $this->belongsTo(HandsOnRegistration::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function isSeminar(): bool
    {
        return $this->activity_type === 'seminar';
    }

    public function isHandsOn(): bool
    {
        return $this->activity_type === 'hands_on';
    }
}
