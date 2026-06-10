<?php

namespace App\Mail;

use App\Models\PosterSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PosterSubmissionConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PosterSubmission $submission) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans('seminar.email_poster_submission_confirmation_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.poster-submission-confirmation',
        );
    }
}
