<?php

use App\Mail\HandsOnRegistrationConfirmation;
use App\Mail\PosterSubmissionConfirmation;
use App\Mail\SeminarPaymentRejected;
use App\Mail\SeminarPaymentVerified;
use App\Mail\SeminarRegistrationConfirmation;
use App\Mail\VisitorRegistrationConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Request-context mail must not block the HTTP response. Mailables that are
 * dispatched from admin pages or Livewire actions implement ShouldQueue, so
 * Mail::send() pushes them onto the queue instead of opening SMTP inline.
 */
it('queues the mailables that are sent from a request context', function () {
    expect(VisitorRegistrationConfirmation::class)->toImplement(ShouldQueue::class)
        ->and(PosterSubmissionConfirmation::class)->toImplement(ShouldQueue::class)
        ->and(SeminarPaymentVerified::class)->toImplement(ShouldQueue::class)
        ->and(SeminarPaymentRejected::class)->toImplement(ShouldQueue::class);
});

it('keeps the submission confirmation mailables synchronous', function () {
    // These stamp confirmation_email_sent_at immediately after send(), so they
    // must stay synchronous. They already run inside the completion jobs, which
    // are themselves queued, so the request is never blocked by them.
    expect(SeminarRegistrationConfirmation::class)->not->toImplement(ShouldQueue::class)
        ->and(HandsOnRegistrationConfirmation::class)->not->toImplement(ShouldQueue::class);
});
