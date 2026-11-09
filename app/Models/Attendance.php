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
        'cancelled_at',
        'cancelled_by',
        'notes',
    ];

    protected $casts = [
        'day' => 'integer',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'cancelled_at' => 'datetime',
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

    /**
     * A cancelled row is kept as evidence but no longer counts as a check-in, so
     * every reader filters on this rather than on `checked_in_at` alone.
     */
    public function isCancelled(): bool
    {
        return $this->cancelled_at !== null;
    }

    public function isCheckedIn(): bool
    {
        return $this->checked_in_at !== null && ! $this->isCancelled();
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
