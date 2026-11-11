<?php

namespace App\Mail;

use App\Models\PosterSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PosterLetterOfAcceptance extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, string>  $ccRecipients
     * @param  array<int, string>  $bccRecipients
     */
    public function __construct(
        public PosterSubmission $submission,
        public array $ccRecipients,
        public array $bccRecipients,
        public string $letterPath,
        public string $letterName,
    ) {
        $this->cc($ccRecipients)->bcc($bccRecipients);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans('seminar.email_poster_loa_subject'),
            cc: $this->ccRecipients,
            bcc: $this->bccRecipients,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.poster-letter-of-acceptance',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk('local', $this->letterPath)
                ->as($this->letterName),
        ];
    }
}
