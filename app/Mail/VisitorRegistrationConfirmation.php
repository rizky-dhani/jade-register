<?php

namespace App\Mail;

use App\Models\Visitor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VisitorRegistrationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Visitor $visitor) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to Jakarta Dental Exhibition 2026!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.visitor-registration-confirmation',
        );
    }
}
