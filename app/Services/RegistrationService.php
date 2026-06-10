<?php

namespace App\Services;

use App\Mail\HandsOnRegistrationConfirmation;
use App\Mail\PosterSubmissionConfirmation;
use App\Mail\SeminarPaymentRejected;
use App\Mail\SeminarPaymentVerified;
use App\Mail\SeminarRegistrationConfirmation;
use App\Mail\VisitorRegistrationConfirmation;
use App\Models\HandsOnRegistration;
use App\Models\PosterSubmission;
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
        $locale = $registration->language ?? 'en';

        Mail::to($registration->email)
            ->locale($locale)
            ->send(new SeminarRegistrationConfirmation($registration));

        $registration->update(['confirmation_email_sent_at' => now()]);
    }

    public function sendHandsOnSubmissionConfirmation(HandsOnRegistration $registration): void
    {
        $locale = $registration->language ?? 'en';

        Mail::to($registration->email)
            ->locale($locale)
            ->send(new HandsOnRegistrationConfirmation($registration));

        $registration->update(['confirmation_email_sent_at' => now()]);
    }

    public function sendPosterSubmissionConfirmation(PosterSubmission $submission): void
    {
        $emails = array_map('trim', explode(',', $submission->author_emails));

        foreach ($emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($email)->send(new PosterSubmissionConfirmation($submission));
            }
        }
    }

    public function sendHandsOnAttendanceConfirmation(HandsOnRegistration $registration): void
    {
        $locale = $registration->language ?? 'en';

        Mail::to($registration->email)
            ->locale($locale)
            ->send(new HandsOnRegistrationConfirmation($registration));

        $registration->update(['confirmation_email_sent_at' => now()]);
    }

    public function sendAttendanceConfirmation(SeminarRegistration $registration): void
    {
        Mail::to($registration->email)
            ->locale($registration->language ?? 'en')
            ->send(new SeminarRegistrationConfirmation($registration));
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
