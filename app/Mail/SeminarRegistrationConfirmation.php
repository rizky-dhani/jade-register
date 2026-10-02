<?php

namespace App\Mail;

use App\Models\SeminarRegistration;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SeminarRegistrationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public string $whatsappGroupUrl;

    public function __construct(public SeminarRegistration $registration)
    {
        $this->whatsappGroupUrl = (string) Setting::get(
            'whatsapp_group_url',
            config('settings.whatsapp_group_url.default')
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans('seminar.email_registration_confirmation_subject', [
                'code' => $this->registration->registration_code,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.seminar-registration-confirmation',
        );
    }
}
