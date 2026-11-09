<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    /** The seminar runs for three days; day 1-3 are the seminar check-in slots. */
    public const SEMINAR_DAYS = [1, 2, 3];

    protected $fillable = [
        'seminar_registration_id',
        'hands_on_registration_id',
        'activity_type',
        'day',
        'checked_in_at',
        'checked_in_by',
        'checked_out_at',
        'checked_out_by',
        'notes',
    ];

    protected $casts = [
        'day' => 'integer',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
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

    public function checkedOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by');
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
