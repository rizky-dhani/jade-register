<?php

namespace App\Filament\Resources\HandsOnRegistrations\Pages;

use App\Filament\Resources\HandsOnRegistrations\HandsOnRegistrationResource;
use App\Models\Country;
use App\Models\HandsOnRegistration;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Collection;

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

        // Standalone registration (not linked to an existing seminar)
        if (empty($data['seminar_registration_id'])) {
            $country = Country::find($data['country_id'] ?? null);
            $data['language'] = $country?->is_indonesia ? 'id' : 'en';
            // registration_type stays as 'hands_on' (form default)
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

        // Create additional HandsOnRegistration records for remaining selections
        foreach ($this->handsOnSelections as $handsOnId) {
            if ((int) $handsOnId === (int) $record->hands_on_id) {
                continue; // Skip the one already used for the main record
            }

            HandsOnRegistration::create([
                'seminar_registration_id' => $record->seminar_registration_id,
                'hands_on_id' => $handsOnId,
                'registration_type' => $record->registration_type,
                'payment_status' => $record->payment_status,
                'payment_proof_path' => $record->payment_proof_path,
                'verified_at' => $record->verified_at,
            ]);
        }
    }
}
