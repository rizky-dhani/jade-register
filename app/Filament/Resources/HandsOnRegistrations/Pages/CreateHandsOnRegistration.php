<?php

namespace App\Filament\Resources\HandsOnRegistrations\Pages;

use App\Filament\Resources\HandsOnRegistrations\HandsOnRegistrationResource;
use App\Models\Country;
use App\Models\HandsOnRegistration;
use App\Services\RegistrationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class CreateHandsOnRegistration extends CreateRecord
{
    protected static string $resource = HandsOnRegistrationResource::class;

    protected Collection $handsOnSelections;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extract per-date hands-on selections from radio groups
        $this->handsOnSelections = collect($data['selectedHandsOn'] ?? [])->filter();
        unset($data['selectedHandsOn']);

        // Map the first selected session to the main record's hands_on_id
        $data['hands_on_id'] = $this->handsOnSelections->first();

        // Generate a unique registration code
        $data['registration_code'] = HandsOnRegistration::generateRegistrationCode();

        // Standalone registration (not linked to an existing seminar)
        if (empty($data['seminar_registration_id'])) {
            $country = Country::find($data['country_id'] ?? null);
            $data['language'] = $country?->is_indonesia ? 'id' : 'en';
            $data['registration_type'] = 'hands_on';
        } else {
            // Linked to an existing seminar registration
            $data['registration_type'] = 'combined';
        }

        // Auto-fill verified_at when status is verified
        if (($data['payment_status'] ?? '') === 'verified') {
            $data['verified_at'] = now();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        // Rename payment proof to use 6-digit registration code (before copies share the path)
        if ($record->payment_proof_path) {
            $codeNumber = substr($record->registration_code, -6);
            $newName = $codeNumber.'.'.pathinfo($record->payment_proof_path, PATHINFO_EXTENSION);
            $newPath = 'payment-proofs/'.$newName;
            if (Storage::disk('public')->exists($record->payment_proof_path)) {
                Storage::disk('public')->move($record->payment_proof_path, $newPath);
                $record->update(['payment_proof_path' => $newPath]);
            }
        }

        // Create additional HandsOnRegistration records for remaining selections
        foreach ($this->handsOnSelections as $handsOnId) {
            if ((int) $handsOnId === (int) $record->hands_on_id) {
                continue; // Skip the one already used for the main record
            }

            HandsOnRegistration::create([
                'registration_code' => HandsOnRegistration::generateRegistrationCode(),
                'seminar_registration_id' => $record->seminar_registration_id,
                'hands_on_id' => $handsOnId,
                'registration_type' => $record->registration_type,
                'payment_status' => $record->payment_status,
                'payment_proof_path' => $record->payment_proof_path,
                'verified_at' => $record->verified_at,
            ]);
        }

        // Send confirmation email
        $registrationService = app(RegistrationService::class);
        $registrationService->sendHandsOnSubmissionConfirmation($record);
    }
}
