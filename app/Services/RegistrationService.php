<?php

namespace App\Services;

use App\Mail\SeminarAttendanceConfirmation;
use App\Mail\SeminarPaymentRejected;
use App\Mail\SeminarPaymentVerified;
use App\Mail\SeminarRegistrationSubmitted;
use App\Mail\VisitorRegistrationConfirmation;
use App\Models\SeminarRegistration;
use App\Models\Visitor;
use Illuminate\Support\Facades\Mail;

class RegistrationService
{
    public function sendVisitorConfirmation(Visitor $visitor): void
    {
        Mail::to($visitor->email)->send(new VisitorRegistrationConfirmation($visitor));
    }

    public function sendSeminarSubmissionConfirmation(SeminarRegistration $registration): void
    {
        // Prevent duplicate emails
        if ($registration->confirmation_email_sent_at) {
            \Illuminate\Support\Facades\Log::warning('Skipping duplicate email send', [
                'registration_code' => $registration->registration_code,
                'email' => $registration->email,
            ]);

            return;
        }

        $locale = $registration->language ?? 'en';

        // Send registration submitted confirmation (with QR code)
        Mail::to($registration->email)
            ->locale($locale)
            ->send(new SeminarRegistrationSubmitted($registration));

        // Send detailed JADE 2026 confirmation (with schedule, notes, SKP info)
        Mail::to($registration->email)
            ->locale($locale)
            ->send(new SeminarAttendanceConfirmation($registration));

        // Mark as sent to prevent duplicates
        $registration->update(['confirmation_email_sent_at' => now()]);
    }

    public function sendAttendanceConfirmation(SeminarRegistration $registration): void
    {
        Mail::to($registration->email)
            ->locale($registration->language ?? 'en')
            ->send(new SeminarAttendanceConfirmation($registration));
    }

    public function sendPaymentVerificationNotification(SeminarRegistration $registration): void
    {
        Mail::to($registration->email)->send(new SeminarPaymentVerified($registration));
    }

    public function sendPaymentRejectionNotification(SeminarRegistration $registration): void
    {
        Mail::to($registration->email)->send(new SeminarPaymentRejected($registration));
    }
}
