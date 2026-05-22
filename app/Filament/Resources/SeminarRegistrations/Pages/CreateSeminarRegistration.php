<?php

namespace App\Filament\Resources\SeminarRegistrations\Pages;

use App\Filament\Resources\SeminarRegistrations\SeminarRegistrationResource;
use App\Models\Country;
use App\Models\SeminarRegistration;
use App\Services\QrTokenService;
use App\Services\RegistrationService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateSeminarRegistration extends CreateRecord
{
    protected static string $resource = SeminarRegistrationResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('filament.notifications.created_title'))
            ->body(__('filament.notifications.created_body'));
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate registration code
        $data['registration_code'] = SeminarRegistration::generateRegistrationCode();

        // Determine language based on selected country (Indonesia = id, others = en)
        $country = Country::find($data['country_id'] ?? null);
        $data['language'] = $country?->is_indonesia ? 'id' : 'en';

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var SeminarRegistration $registration */
        $registration = $this->record;

        // Rename payment proof to use 6-digit registration code
        if ($registration->payment_proof_path) {
            $codeNumber = substr($registration->registration_code, -6);
            $newName = $codeNumber.'.'.pathinfo($registration->payment_proof_path, PATHINFO_EXTENSION);
            $newPath = 'payment-proofs/'.$newName;
            if (Storage::disk('public')->exists($registration->payment_proof_path)) {
                Storage::disk('public')->move($registration->payment_proof_path, $newPath);
                $registration->update(['payment_proof_path' => $newPath]);
            }
        }

        // Generate QR token for attendance check-in
        $qrTokenService = app(QrTokenService::class);
        $qrTokenService->generate($registration);

        // Send confirmation email (same as Livewire)
        $registrationService = app(RegistrationService::class);
        $registrationService->sendSeminarSubmissionConfirmation($registration);
    }

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }
}
