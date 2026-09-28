<?php

namespace App\Jobs;

use App\Models\SeminarRegistration;
use App\Services\QrTokenService;
use App\Services\RegistrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CompleteSeminarRegistration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 60];

    public function __construct(public SeminarRegistration $registration)
    {
        $this->afterCommit();
    }

    public function handle(QrTokenService $qrTokenService, RegistrationService $registrationService): void
    {
        $registration = $this->registration->fresh();

        if (! $registration) {
            return;
        }

        if (! $registration->qr_token) {
            $qrTokenService->generate($registration);
        }

        if (! $registration->confirmation_email_sent_at) {
            $registrationService->sendSeminarSubmissionConfirmation($registration->fresh());
        }
    }
}
