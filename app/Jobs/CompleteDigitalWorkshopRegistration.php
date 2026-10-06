<?php

namespace App\Jobs;

use App\Models\DigitalWorkshopRegistration;
use App\Services\QrTokenService;
use App\Services\RegistrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CompleteDigitalWorkshopRegistration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 60];

    public function __construct(public DigitalWorkshopRegistration $registration)
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
            $qrTokenService->generateForDigitalWorkshop($registration);
        }

        if (! $registration->confirmation_email_sent_at) {
            $registrationService->sendDigitalWorkshopSubmissionConfirmation($registration->fresh());
        }
    }
}
