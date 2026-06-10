<?php

namespace App\Console\Commands;

use App\Models\AddonRegistration;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use App\Services\RegistrationService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerifyPayment extends Command
{
    protected $signature = 'payments:verify
                            {registration_code? : The registration code to verify or reject (optional with --all)}
                            {--model=seminar : Model to check: seminar, handson, addon, or all}
                            {--verify : Mark payment as verified (default)}
                            {--reject= : Mark payment as rejected with optional reason}
                            {--all : Verify all pending payments for the selected model}
                            {--dry-run : Preview changes without making them}';

    protected $description = 'Verify or reject pending seminar/handson/addon registration payments';

    public function handle(): int
    {
        $code = $this->argument('registration_code');
        $model = $this->option('model');
        $isDryRun = $this->option('dry-run');

        // Show pending counts if no specific code provided and not using --all
        if (! $code && ! $this->option('all')) {
            return $this->showPendingCounts();
        }

        // Batch verify all pending
        if ($this->option('all')) {
            return $this->verifyAllPending($model, $isDryRun);
        }

        // Single registration verification
        $registration = $this->findRegistration($model, $code);

        if (! $registration) {
            $this->error("Registration with code '{$code}' not found in {$model}.");
            $this->info('Use --model=seminar, --model=handson, or --model=addon to search different models.');

            return Command::FAILURE;
        }

        $currentStatus = $registration->payment_status;

        if ($currentStatus === 'verified') {
            $this->warn("Registration '{$code}' is already verified.");
            $this->displayRegistrationDetails($registration, $model);

            return Command::SUCCESS;
        }

        $this->info("Found registration: {$code}");
        $this->displayRegistrationDetails($registration, $model);

        if ($isDryRun) {
            $this->warn('[DRY RUN] No changes made.');

            return Command::SUCCESS;
        }

        if ($this->option('reject') !== null) {
            $reason = $this->option('reject');
            $this->processReject($registration, $reason, $model);

            return Command::SUCCESS;
        }

        if ($this->option('verify') || (! $this->option('reject'))) {
            $this->processVerify($registration, $model);
        }

        return Command::SUCCESS;
    }

    private function showPendingCounts(): int
    {
        $this->info('=== Pending Payment Counts ===');
        $this->newLine();

        $counts = $this->getPendingCounts();

        $headers = ['Model', 'Pending', 'Verified', 'Rejected', 'Total'];
        $rows = [];

        foreach ($counts as $model => $data) {
            $rows[] = [
                ucfirst($model),
                $data['pending'],
                $data['verified'],
                $data['rejected'],
                $data['total'],
            ];
        }

        $this->table($headers, $rows);
        $this->newLine();
        $this->info('To verify all pending payments:');
        $this->line('  php artisan payments:verify --all --model=seminar');
        $this->line('  php artisan payments:verify --all --model=all');

        return Command::SUCCESS;
    }

    private function getPendingCounts(): array
    {
        return [
            'seminar' => [
                'pending' => SeminarRegistration::where('payment_status', 'pending')->count(),
                'verified' => SeminarRegistration::where('payment_status', 'verified')->count(),
                'rejected' => SeminarRegistration::where('payment_status', 'rejected')->count(),
                'total' => SeminarRegistration::count(),
            ],
            'handson' => [
                'pending' => HandsOnRegistration::where('payment_status', 'pending')->count(),
                'verified' => HandsOnRegistration::where('payment_status', 'verified')->count(),
                'rejected' => HandsOnRegistration::where('payment_status', 'rejected')->count(),
                'total' => HandsOnRegistration::count(),
            ],
            'addon' => [
                'pending' => AddonRegistration::where('payment_status', 'pending')->count(),
                'verified' => AddonRegistration::where('payment_status', 'verified')->count(),
                'rejected' => AddonRegistration::where('payment_status', 'rejected')->count(),
                'total' => AddonRegistration::count(),
            ],
        ];
    }

    private function verifyAllPending(string $model, bool $isDryRun): int
    {
        $models = $model === 'all' ? ['seminar', 'handson', 'addon'] : [$model];

        $totalVerified = 0;

        foreach ($models as $m) {
            $pending = $this->getPendingQuery($m)->get();

            if ($pending->isEmpty()) {
                $this->warn("No pending payments for {$m}.");

                continue;
            }

            $this->info("Found {$pending->count()} pending {$m} registration(s).");

            if ($isDryRun) {
                $this->warn('[DRY RUN] Would verify:');
                $this->displayPendingList($pending, $m);

                continue;
            }

            if (! $this->confirm("Verify all {$pending->count()} pending {$m} payments?")) {
                $this->info("Skipped {$m}.");

                continue;
            }

            $verified = $this->batchVerify($pending, $m);
            $totalVerified += $verified;
        }

        $this->newLine();
        $this->info("✓ Total verified: {$totalVerified}");

        return Command::SUCCESS;
    }

    private function getPendingQuery(string $model): Builder
    {
        return match ($model) {
            'seminar' => SeminarRegistration::where('payment_status', 'pending'),
            'handson' => HandsOnRegistration::where('payment_status', 'pending'),
            'addon' => AddonRegistration::where('payment_status', 'pending'),
            default => throw new \InvalidArgumentException("Unknown model: {$model}"),
        };
    }

    private function displayPendingList($registrations, string $model): void
    {
        $rows = $registrations->map(function ($reg) {
            return [
                $reg->registration_code,
                $reg->email,
                $reg->name ?? 'N/A',
                $reg->created_at->format('Y-m-d'),
            ];
        })->toArray();

        $this->table(['Code', 'Email', 'Name', 'Created'], $rows);
    }

    private function batchVerify($registrations, string $model): int
    {
        $verified = 0;
        $errors = 0;

        foreach ($registrations as $registration) {
            try {
                DB::transaction(function () use ($registration) {
                    $registration->update([
                        'payment_status' => 'verified',
                        'verified_at' => now(),
                    ]);
                });

                $this->info("  ✓ {$registration->registration_code}");

                if ($registration instanceof SeminarRegistration) {
                    $this->sendVerificationEmail($registration);
                }

                Log::info('Payment verified via batch command', [
                    'model' => $model,
                    'registration_code' => $registration->registration_code,
                ]);

                $verified++;
            } catch (\Throwable $e) {
                $this->error("  ✗ {$registration->registration_code}: {$e->getMessage()}");
                $errors++;
            }
        }

        return $verified;
    }

    private function findRegistration(string $model, string $code): ?Model
    {
        return match ($model) {
            'seminar' => SeminarRegistration::where('registration_code', $code)->first(),
            'handson' => HandsOnRegistration::where('registration_code', $code)->first(),
            'addon' => AddonRegistration::whereHas('seminarRegistration', function ($query) use ($code) {
                $query->where('registration_code', $code);
            })->first(),
            default => null,
        };
    }

    private function displayRegistrationDetails(Model $registration, string $model): void
    {
        $data = match (true) {
            $registration instanceof SeminarRegistration => [
                'Type' => 'Seminar Registration',
                'Email' => $registration->email,
                'Name' => $registration->name,
                'Amount' => $registration->formatted_amount,
                'Payment Status' => $registration->payment_status,
                'Payment Method' => $registration->payment_method,
                'Payment Proof' => $registration->payment_proof_path ? 'Yes' : 'No',
                'Created' => $registration->created_at->format('Y-m-d H:i:s'),
            ],
            $registration instanceof HandsOnRegistration => [
                'Type' => 'Hands On Registration',
                'Email' => $registration->email ?? $registration->seminarRegistration?->email,
                'Name' => $registration->name ?? $registration->seminarRegistration?->name,
                'Amount' => number_format($registration->handsOn->price ?? 0, 0, ',', '.'),
                'Payment Status' => $registration->payment_status,
                'Event' => $registration->handsOn->name ?? 'N/A',
            ],
            $registration instanceof AddonRegistration => [
                'Type' => 'Addon Registration',
                'Email' => $registration->seminarRegistration?->email,
                'Name' => $registration->seminarRegistration?->name,
                'Addon' => $registration->addon?->name ?? 'N/A',
                'Amount' => number_format($registration->amount, 0, ',', '.'),
                'Payment Status' => $registration->payment_status,
            ],
            default => [],
        };

        $this->table(array_keys($data), [array_values($data)]);
    }

    private function processVerify(Model $registration, string $model): void
    {
        if (! $this->confirm('Mark this payment as VERIFIED?')) {
            $this->info('Verification cancelled.');

            return;
        }

        DB::transaction(function () use ($registration) {
            $registration->update([
                'payment_status' => 'verified',
                'verified_at' => now(),
            ]);
        });

        $this->info("✓ Payment verified for registration: {$registration->registration_code}");

        Log::info('Payment verified via command', [
            'model' => $model,
            'registration_code' => $registration->registration_code,
        ]);

        if ($registration instanceof SeminarRegistration) {
            $this->sendVerificationEmail($registration);
        }
    }

    private function processReject(Model $registration, ?string $reason, string $model): void
    {
        $confirmed = $this->confirm('Mark this payment as REJECTED?'."\n".'Reason: '.($reason ?: 'Not provided'));

        if (! $confirmed) {
            $this->info('Rejection cancelled.');

            return;
        }

        $updateData = [
            'payment_status' => 'rejected',
        ];

        if ($registration instanceof SeminarRegistration) {
            $updateData['rejection_reason'] = $reason;
            $updateData['verified_at'] = now();
        }

        DB::transaction(function () use ($registration, $updateData) {
            $registration->update($updateData);
        });

        $this->info("✗ Payment rejected for registration: {$registration->registration_code}");

        if ($reason) {
            $this->line("Reason: {$reason}");
        }

        Log::info('Payment rejected via command', [
            'model' => $model,
            'registration_code' => $registration->registration_code,
            'reason' => $reason,
        ]);

        if ($registration instanceof SeminarRegistration) {
            $this->sendRejectionEmail($registration);
        }
    }

    private function sendVerificationEmail(SeminarRegistration $registration): void
    {
        try {
            $service = app(RegistrationService::class);
            $service->sendPaymentVerificationNotification($registration);
            $this->info('  → Verification email sent.');
        } catch (\Throwable $e) {
            $this->warn('  → Failed to send verification email: '.$e->getMessage());
            Log::error('Failed to send verification email', [
                'registration_code' => $registration->registration_code,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendRejectionEmail(SeminarRegistration $registration): void
    {
        try {
            $service = app(RegistrationService::class);
            $service->sendPaymentRejectionNotification($registration);
            $this->info('  → Rejection email sent.');
        } catch (\Throwable $e) {
            $this->warn('  → Failed to send rejection email: '.$e->getMessage());
            Log::error('Failed to send rejection email', [
                'registration_code' => $registration->registration_code,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
