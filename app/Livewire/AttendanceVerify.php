<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\SeminarRegistration;
use App\Services\QrTokenService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AttendanceVerify extends Component
{
    public string $token;

    public ?SeminarRegistration $registration = null;

    public bool $isValid = true;

    public bool $isExpired = false;

    public bool $showSuccess = false;

    public ?string $seminarCheckedInAt = null;

    public array $handsOnCheckedIn = [];

    protected static string $view = 'livewire.attendance-verify';

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
    }

    public function loadCheckInStatus(): void
    {
        $seminarAttendance = Attendance::where('seminar_registration_id', $this->registration->id)
            ->where('activity_type', 'seminar')
            ->first();

        if ($seminarAttendance) {
            $this->seminarCheckedInAt = $seminarAttendance->checked_in_at->format('d M Y H:i');
        }

        $handsOnAttendances = Attendance::where('seminar_registration_id', $this->registration->id)
            ->where('activity_type', 'hands_on')
            ->with('handsOnRegistration.handsOn')
            ->get();

        foreach ($handsOnAttendances as $attendance) {
            $this->handsOnCheckedIn[$attendance->hands_on_registration_id] = $attendance->checked_in_at->format('d M Y H:i');
        }
    }

    public function checkInSeminar(): void
    {
        if ($this->seminarCheckedInAt) {
            return;
        }

        if ($this->registration->payment_status !== 'verified') {
            $this->addError('payment', __('seminar.payment_not_verified'));

            return;
        }

        Attendance::create([
            'seminar_registration_id' => $this->registration->id,
            'activity_type' => 'seminar',
            'checked_in_at' => now(),
            'checked_in_by' => Auth::id(),
        ]);

        $this->seminarCheckedInAt = now()->format('d M Y H:i');
        $this->showSuccess = true;
    }

    public function checkInHandsOn(int $handsOnRegistrationId): void
    {
        if (isset($this->handsOnCheckedIn[$handsOnRegistrationId])) {
            return;
        }

        $handsOnReg = $this->registration->handsOnRegistrations()->find($handsOnRegistrationId);

        if (! $handsOnReg || $handsOnReg->payment_status !== 'verified') {
            $this->addError('hands_on_'.$handsOnRegistrationId, __('seminar.hands_on_payment_not_verified'));

            return;
        }

        Attendance::create([
            'seminar_registration_id' => $this->registration->id,
            'hands_on_registration_id' => $handsOnRegistrationId,
            'activity_type' => 'hands_on',
            'checked_in_at' => now(),
            'checked_in_by' => Auth::id(),
        ]);

        $this->handsOnCheckedIn[$handsOnRegistrationId] = now()->format('d M Y H:i');
        $this->showSuccess = true;
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

    public function getHandsOnSessionsProperty()
    {
        return $this->registration->handsOnRegistrations()
            ->with('handsOn')
            ->get()
            ->map(fn ($reg) => [
                'id' => $reg->id,
                'name' => $reg->handsOn->name,
                'date' => $reg->handsOn->event_date->format('d M Y'),
                'payment_status' => $reg->payment_status,
                'checked_in' => $this->handsOnCheckedIn[$reg->id] ?? null,
            ]);
    }

    public function render()
    {
        return view('livewire.attendance-verify');
    }
}
