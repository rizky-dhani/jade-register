<?php

namespace App\Mail;

use App\Models\HandsOnRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HandsOnRegistrationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public HandsOnRegistration $registration) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans('seminar.email_hands_on_registration_confirmation_subject', [
                'code' => $this->registration->registration_code,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.hands-on-registration-confirmation',
        );
    }
}
