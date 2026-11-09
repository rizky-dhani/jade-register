<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * Read-only rows of the `attendance_roster` database view: one row per
 * attendance-desk participant, covering both seminar registrations and
 * standalone hands-on registrations.
 *
 * Writes go to SeminarRegistration / HandsOnRegistration — this model exists so
 * Filament can filter, search and paginate the combined list, and so the two
 * sides can be eager loaded through relations rather than per-cell queries.
 */
class AttendanceRoster extends Model
{
    protected $table = 'attendance_roster';

    protected $primaryKey = 'participant_key';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    public function seminarRegistration(): BelongsTo
    {
        return $this->belongsTo(SeminarRegistration::class, 'seminar_registration_id');
    }

    /**
     * The single hands-on session this row stands for. Only populated on
     * `hands_on` rows — a standalone hands-on registration has no seminar
     * registration to hang off.
     */
    public function handsOnRegistration(): BelongsTo
    {
        return $this->belongsTo(HandsOnRegistration::class, 'hands_on_registration_id');
    }

    /**
     * The participant's hands-on sessions, whichever kind of row this is:
     * the row's own registration for a standalone one, otherwise the seminar
     * registration's sessions.
     *
     * @return Collection<int, HandsOnRegistration>
     */
    public function resolvedHandsOnRegistrations(): Collection
    {
        if ($this->activity_type === 'hands_on') {
            return $this->handsOnRegistration
                ? new Collection([$this->handsOnRegistration])
                : new Collection;
        }

        return $this->seminarRegistration?->verifiedHandsOnRegistrations ?? new Collection;
    }

    public function isSeminar(): bool
    {
        return $this->activity_type === 'seminar';
    }

    public function seminarAttendanceForDay(int $day): ?Attendance
    {
        return $this->seminarRegistration?->seminarAttendances->firstWhere('day', $day);
    }
}
