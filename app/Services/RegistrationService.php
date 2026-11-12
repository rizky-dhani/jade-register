<?php

namespace App\Services;

use App\Mail\DigitalWorkshopRegistrationConfirmation;
use App\Mail\HandsOnRegistrationConfirmation;
use App\Mail\PosterFileRequest;
use App\Mail\PosterLetterOfAcceptance;
use App\Mail\PosterSubmissionConfirmation;
use App\Mail\SeminarPaymentRejected;
use App\Mail\SeminarPaymentVerified;
use App\Mail\SeminarRegistrationConfirmation;
use App\Mail\VisitorRegistrationConfirmation;
use App\Models\DigitalWorkshopRegistration;
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

    /** Request contexts pass $queue = true so SMTP never blocks the response. */
    public function sendSeminarSubmissionConfirmation(SeminarRegistration $registration, bool $queue = false): void
    {
        $locale = $registration->language ?? 'en';
        $mailable = new SeminarRegistrationConfirmation($registration);
        $mailer = Mail::to($registration->email)->locale($locale);

        if ($queue) {
            $mailer->queue($mailable);
        } else {
            $mailer->send($mailable);
        }

        $registration->update(['confirmation_email_sent_at' => now()]);
    }

    /** Request contexts pass $queue = true so SMTP never blocks the response. */
    public function sendHandsOnSubmissionConfirmation(HandsOnRegistration $registration, bool $queue = false): void
    {
        $email = $registration->recipientEmail();

        if ($email === null) {
            return;
        }

        $mailable = new HandsOnRegistrationConfirmation($registration);
        $mailer = Mail::to($email)->locale($registration->recipientLanguage());

        if ($queue) {
            $mailer->queue($mailable);
        } else {
            $mailer->send($mailable);
        }

        $registration->update(['confirmation_email_sent_at' => now()]);
    }

    public function sendDigitalWorkshopSubmissionConfirmation(DigitalWorkshopRegistration $registration): void
    {
        $email = $registration->recipientEmail();

        if ($email === null) {
            return;
        }

        Mail::to($email)
            ->locale($registration->recipientLanguage())
            ->send(new DigitalWorkshopRegistrationConfirmation($registration));

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

    /**
     * Ask the authors to re-upload the poster file when nothing was stored.
     *
     * @return int number of recipients the request was sent to
     */
    public function sendPosterFileRequest(PosterSubmission $submission): int
    {
        $locale = $submission->seminarRegistration?->language ?? 'id';
        $emails = array_map('trim', explode(',', (string) $submission->author_emails));

        $sent = 0;

        foreach (array_unique(array_filter($emails)) as $email) {
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            Mail::to($email)
                ->locale($locale)
                ->queue(new PosterFileRequest($submission));

            $sent++;
        }

        return $sent;
    }

    /**
     * Send the Letter of Acceptance with the signed letter attached.
     *
     * @param  array<int, string>  $to
     * @param  array<int, string>  $cc
     * @param  array<int, string>  $bcc
     * @return int number of primary recipients the letter was queued for
     */
    public function sendPosterLetterOfAcceptance(
        PosterSubmission $submission,
        array $to,
        array $cc,
        array $bcc,
        string $letterPath,
        string $letterName,
    ): int {
        $recipients = $this->validEmailAddresses($to);

        if ($recipients === []) {
            return 0;
        }

        Mail::to($recipients)
            ->locale($submission->seminarRegistration?->language ?? 'id')
            ->queue(new PosterLetterOfAcceptance(
                $submission,
                $this->validEmailAddresses($cc),
                $this->validEmailAddresses($bcc),
                $letterPath,
                $letterName,
            ));

        return count($recipients);
    }

    /**
     * @param  array<int, string>  $emails
     * @return array<int, string>
     */
    private function validEmailAddresses(array $emails): array
    {
        $valid = array_filter(
            array_map('trim', $emails),
            fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false,
        );

        return array_values(array_unique($valid));
    }

    public function sendHandsOnAttendanceConfirmation(HandsOnRegistration $registration): void
    {
        $email = $registration->recipientEmail();

        if ($email === null) {
            return;
        }

        Mail::to($email)
            ->locale($registration->recipientLanguage())
            ->send(new HandsOnRegistrationConfirmation($registration));

        $registration->update(['confirmation_email_sent_at' => now()]);
    }

    public function sendAttendanceConfirmation(SeminarRegistration $registration): void
    {
        Mail::to($registration->email)
            ->locale($registration->language ?? 'en')
            ->queue(new SeminarRegistrationConfirmation($registration));
    }

    /**
     * Resend the confirmation emails a participant already received, for the
     * public self-service page.
     *
     * The address is the identity: seminar_registrations.email is unique, and a
     * hands-on row either carries its own address or falls back to the parent
     * seminar registration's. Every match is resent, since one participant can
     * hold a seminar registration plus several hands-on sessions.
     *
     * Returns the number of emails queued; the caller shows the same response
     * either way so the form can't be used to probe registered addresses.
     */
    public function resendConfirmationFor(string $email): int
    {
        $email = mb_strtolower(trim($email));

        $seminar = SeminarRegistration::query()
            ->whereRaw('lower(email) = ?', [$email])
            ->get();

        foreach ($seminar as $registration) {
            $this->sendSeminarSubmissionConfirmation($registration, queue: true);
        }

        $handsOn = HandsOnRegistration::query()
            ->where(function ($query) use ($email) {
                $query->whereRaw('lower(email) = ?', [$email])
                    ->orWhereHas('seminarRegistration', fn ($q) => $q->whereRaw('lower(email) = ?', [$email]));
            })
            ->get();

        foreach ($handsOn as $registration) {
            $this->sendHandsOnSubmissionConfirmation($registration, queue: true);
        }

        return $seminar->count() + $handsOn->count();
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
