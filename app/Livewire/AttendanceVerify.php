<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\DigitalWorkshopRegistration;
use App\Models\HandsOnRegistration;
use App\Models\LunchRedemption;
use App\Models\SeminarRegistration;
use App\Services\AttendanceCheckInService;
use App\Services\AttendanceCheckOutService;
use App\Services\LunchRedemptionService;
use App\Services\QrTokenService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AttendanceVerify extends Component
{
    public string $token;

    public SeminarRegistration|HandsOnRegistration|DigitalWorkshopRegistration|null $registration = null;

    public bool $isValid = true;

    public bool $isExpired = false;

    public bool $showSuccess = false;

    public bool $showCheckOutSuccess = false;

    /**
     * The participant's seminar check-in rows keyed by event day, e.g.
     * `[1 => '13 Nov 2026 09:12', 2 => null, 3 => null]`.
     *
     * @var array<int, string|null>
     */
    public array $seminarDayCheckedIn = [];

    /**
     * The participant's seminar check-out stamps keyed by event day, e.g.
     * `[1 => '13 Nov 2026 17:02', 2 => null, 3 => null]`.
     *
     * @var array<int, string|null>
     */
    public array $seminarDayCheckedOut = [];

    /**
     * The latest check-out across the participant's seminar days, kept for the
     * summary line above the day list.
     */
    public ?string $seminarCheckedOutAt = null;

    public array $handsOnCheckedOut = [];

    public array $handsOnCheckedIn = [];

    protected static string $view = 'livewire.attendance-verify';

    public ?LunchRedemption $lunchRedemption = null;

    public bool $lunchRedemptionSuccess = false;

    public function mount(string $token): void
    {
        $this->token = $token;

        $qrTokenService = app(QrTokenService::class);
        $this->registration = $qrTokenService->validate($token);

        if (! $this->registration) {
            $this->isValid = false;

            return;
        }

        if ($qrTokenService->isExpired($this->registration)) {
            $this->isExpired = true;
            $this->isValid = false;

            return;
        }

        $this->loadCheckInStatus();
        if ($this->registration instanceof SeminarRegistration) {
            $this->lunchRedemption = $this->registration->lunchRedemption()->with('redeemedBy')->first();
        }
    }

    public function loadCheckInStatus(): void
    {
        if ($this->registration instanceof HandsOnRegistration) {
            $attendance = Attendance::where('hands_on_registration_id', $this->registration->id)
                ->where('activity_type', 'hands_on')
                ->whereNull('cancelled_at')
                ->first();

            if ($attendance) {
                $this->handsOnCheckedIn[$this->registration->id] = $attendance->checked_in_at->format('d M Y H:i');

                if ($attendance->checked_out_at) {
                    $this->handsOnCheckedOut[$this->registration->id] = $attendance->checked_out_at->format('d M Y H:i');
                }
            }

            return;
        }

        // A Digital Workshop registration has no seminar attendance of its own.
        // Without this guard its id would be used as a seminar_registration_id
        // and match an unrelated participant's attendance record.
        if ($this->registration instanceof DigitalWorkshopRegistration) {
            return;
        }

        // Cancelled check-ins are excluded: a cancelled day reads as open again.
        $seminarAttendances = Attendance::where('seminar_registration_id', $this->registration->id)
            ->where('activity_type', 'seminar')
            ->whereNull('hands_on_registration_id')
            ->whereNull('cancelled_at')
            ->orderBy('day')
            ->get();

        // Every event day starts un-checked so the desk always sees three slots,
        // including the days that have no attendance row yet.
        foreach (Attendance::SEMINAR_DAYS as $day) {
            $this->seminarDayCheckedIn[$day] = null;
            $this->seminarDayCheckedOut[$day] = null;
        }

        foreach ($seminarAttendances as $attendance) {
            $this->seminarDayCheckedIn[$attendance->day] = $attendance->checked_in_at->format('d M Y H:i');

            if ($attendance->checked_out_at) {
                $this->seminarDayCheckedOut[$attendance->day] = $attendance->checked_out_at->format('d M Y H:i');
                $this->seminarCheckedOutAt = $attendance->checked_out_at->format('d M Y H:i');
            }
        }

        $handsOnAttendances = Attendance::where('seminar_registration_id', $this->registration->id)
            ->where('activity_type', 'hands_on')
            ->whereNull('cancelled_at')
            ->with('handsOnRegistration.handsOn')
            ->get();

        foreach ($handsOnAttendances as $attendance) {
            $this->handsOnCheckedIn[$attendance->hands_on_registration_id] = $attendance->checked_in_at->format('d M Y H:i');

            if ($attendance->checked_out_at) {
                $this->handsOnCheckedOut[$attendance->hands_on_registration_id] = $attendance->checked_out_at->format('d M Y H:i');
            }
        }
    }

    public function lunchEligible(): bool
    {
        return $this->registration instanceof SeminarRegistration
            && $this->registration->isEligibleForLunch();
    }

    public function canRedeemLunch(): bool
    {
        return $this->canCheckIn();
    }

    /**
     * Only desk staff may confirm attendance. Participants own accounts of their
     * own and the verify URL is printed in their confirmation email, so without
     * this they could check themselves in from anywhere.
     */
    public function canCheckIn(): bool
    {
        return Auth::user()?->can('checkIn', Attendance::class) ?? false;
    }

    public function canCheckOut(): bool
    {
        return Auth::user()?->can('checkOut', Attendance::class) ?? false;
    }

    public function redeemLunch(): void
    {
        if (! $this->registration instanceof SeminarRegistration) {
            return;
        }

        if (! $this->canRedeemLunch()) {
            $this->addError('lunch', __('lunch-coupon.forbidden'));

            return;
        }

        try {
            $this->lunchRedemption = app(LunchRedemptionService::class)
                ->redeem($this->registration, Auth::user());
        } catch (\DomainException) {
            $this->addError('lunch', __('lunch-coupon.unavailable'));

            return;
        }

        $this->lunchRedemptionSuccess = true;
    }

    public function isLunchRedemptionOpen(): bool
    {
        return app(LunchRedemptionService::class)->isRedemptionOpen();
    }

    /**
     * Check the participant in for one day of the seminar.
     *
     * The event runs for three days, so each day is a separate check-in with its
     * own attendance row. A second tap on an already-checked-in day is a no-op.
     */
    public function checkInSeminarDay(int $day): void
    {
        if (! $this->canCheckIn()) {
            $this->addError('auth', __('seminar.admin_only'));

            return;
        }

        if (! $this->registration instanceof SeminarRegistration) {
            return;
        }

        if (! in_array($day, Attendance::SEMINAR_DAYS, true)) {
            return;
        }

        if ($this->seminarDayCheckedIn[$day] ?? null) {
            return;
        }

        if ($this->registration->payment_status !== 'verified') {
            $this->addError('payment', __('seminar.payment_not_verified'));

            return;
        }

        $staff = Auth::user();

        if (! $staff) {
            return;
        }

        $attendance = app(AttendanceCheckInService::class)->checkInSeminar($this->registration, $staff, $day);

        $this->seminarDayCheckedIn[$day] = $attendance->checked_in_at->format('d M Y H:i');
        $this->showSuccess = true;
    }

    public function checkInHandsOn(int $handsOnRegistrationId): void
    {
        if (! $this->canCheckIn()) {
            $this->addError('auth', __('seminar.admin_only'));

            return;
        }

        if (isset($this->handsOnCheckedIn[$handsOnRegistrationId])) {
            return;
        }

        $handsOnReg = $this->registration instanceof HandsOnRegistration
            ? $this->registration
            : $this->registration->handsOnRegistrations()->find($handsOnRegistrationId);

        if (! $handsOnReg || $handsOnReg->payment_status !== 'verified') {
            $this->addError('hands_on_'.$handsOnRegistrationId, __('seminar.hands_on_payment_not_verified'));

            return;
        }

        $staff = Auth::user();

        if (! $staff) {
            return;
        }

        app(AttendanceCheckInService::class)->checkInHandsOn($handsOnReg, $staff);

        $this->handsOnCheckedIn[$handsOnReg->id] = now()->format('d M Y H:i');
        $this->showSuccess = true;
    }

    /**
     * Check-out is the closing half of the same desk flow, so it is gated the
     * same way as check-in and only applies to a record that is already in.
     */
    /**
     * Check the participant out of one event day.
     *
     * Check-out closes a day, so it is per-day like check-in: the participant
     * may leave after day 1 while still attending day 2.
     */
    public function checkOutSeminarDay(int $day): void
    {
        if (! $this->canCheckOut()) {
            $this->addError('auth', __('seminar.admin_only'));

            return;
        }

        if (! $this->registration instanceof SeminarRegistration) {
            return;
        }

        if (! in_array($day, Attendance::SEMINAR_DAYS, true)) {
            return;
        }

        // Already checked out: idempotent, keep the first stamp.
        if ($this->seminarDayCheckedOut[$day] ?? null) {
            return;
        }

        // Nothing to close until the day has been checked in.
        if (! ($this->seminarDayCheckedIn[$day] ?? null)) {
            $this->addError('check_out', __('seminar.check_out_requires_check_in'));

            return;
        }

        $staff = Auth::user();

        if (! $staff || ! app(AttendanceCheckOutService::class)->checkOutSeminar($this->registration, $staff, $day)) {
            $this->addError('check_out', __('seminar.check_out_requires_check_in'));

            return;
        }

        $this->seminarDayCheckedOut[$day] = now()->format('d M Y H:i');
        $this->seminarCheckedOutAt = $this->seminarDayCheckedOut[$day];
        $this->showCheckOutSuccess = true;
    }

    public function checkOutHandsOn(int $handsOnRegistrationId): void
    {
        if (! $this->canCheckIn()) {
            $this->addError('auth', __('seminar.admin_only'));

            return;
        }

        if (isset($this->handsOnCheckedOut[$handsOnRegistrationId])) {
            return;
        }

        $handsOnReg = $this->registration instanceof HandsOnRegistration
            ? $this->registration
            : $this->registration->handsOnRegistrations()->find($handsOnRegistrationId);

        $staff = Auth::user();

        if (! $handsOnReg || ! $staff || ! app(AttendanceCheckOutService::class)->checkOutHandsOn($handsOnReg, $staff)) {
            $this->addError('hands_on_check_out_'.$handsOnRegistrationId, __('seminar.check_out_requires_check_in'));

            return;
        }

        $this->handsOnCheckedOut[$handsOnReg->id] = now()->format('d M Y H:i');
        $this->showCheckOutSuccess = true;
    }

    public function getPaymentStatusLabelProperty(): string
    {
        return match ($this->registration->payment_status) {
            'verified' => __('seminar.payment_status_verified_label'),
            'pending' => __('seminar.payment_status_pending_label'),
            'rejected' => __('seminar.payment_status_rejected_label'),
            default => __('seminar.payment_status_unknown_label'),
        };
    }

    public function getPaymentStatusColorProperty(): string
    {
        return match ($this->registration->payment_status) {
            'verified' => 'green',
            'pending' => 'yellow',
            'rejected' => 'red',
            default => 'gray',
        };
    }

    public function getHandsOnSessionsProperty(): Collection
    {
        if ($this->registration instanceof HandsOnRegistration) {
            $handsOn = $this->registration->handsOn;

            if (! $handsOn) {
                return collect();
            }

            return collect([
                [
                    'id' => $this->registration->id,
                    'name' => $handsOn->name,
                    'date' => $handsOn->event_date->format('d M Y'),
                    'payment_status' => $this->registration->payment_status,
                    'checked_in' => $this->handsOnCheckedIn[$this->registration->id] ?? null,
                    'checked_out' => $this->handsOnCheckedOut[$this->registration->id] ?? null,
                ],
            ]);
        }

        // A Digital Workshop registration has no hands-on sessions.
        if ($this->registration instanceof DigitalWorkshopRegistration) {
            return collect();
        }

        return $this->registration->handsOnRegistrations()
            ->with('handsOn')
            ->get()
            ->map(fn ($reg) => [
                'id' => $reg->id,
                'name' => $reg->handsOn->name,
                'date' => $reg->handsOn->event_date->format('d M Y'),
                'payment_status' => $reg->payment_status,
                'checked_in' => $this->handsOnCheckedIn[$reg->id] ?? null,
                'checked_out' => $this->handsOnCheckedOut[$reg->id] ?? null,
            ]);
    }

    public function render()
    {
        return view('livewire.attendance-verify')->title(__('seminar.attendance_verification_title').' - '.config('app.name'));
    }
}
