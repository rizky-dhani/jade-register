<?php

namespace App\Mail;

use App\Models\DigitalWorkshopRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DigitalWorkshopRegistrationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DigitalWorkshopRegistration $registration) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans('seminar.email_digital_workshop_confirmation_subject', [
                'code' => $this->registration->registration_code,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.digital-workshop-registration-confirmation',
        );
    }
}
