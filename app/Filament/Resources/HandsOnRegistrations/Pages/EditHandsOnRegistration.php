<?php

namespace App\Filament\Resources\HandsOnRegistrations\Pages;

use App\Filament\Resources\HandsOnRegistrations\HandsOnRegistrationResource;
use App\Models\HandsOnRegistration;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditHandsOnRegistration extends EditRecord
{
    protected static string $resource = HandsOnRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Pre-populate the per-date hands-on radio from the record's current session
        if ($this->record->hands_on_id) {
            $handsOn = $this->record->handsOn;

            if ($handsOn) {
                $data['selectedHandsOn'][$handsOn->event_date->format('Y-m-d')] = $handsOn->id;
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Map the selected hands-on session to the main record's hands_on_id.
        // The pre-filled radio for the current session stays selected, so pick
        // the selection that differs from the record's current session.
        $selectedHandsOn = collect($data['selectedHandsOn'] ?? [])->filter();
        unset($data['selectedHandsOn']);

        if ($selectedHandsOn->isNotEmpty()) {
            $data['hands_on_id'] = $selectedHandsOn
                ->first(fn (mixed $id): bool => (int) $id !== (int) $this->record->hands_on_id)
                ?? $selectedHandsOn->first();
        }

        // Auto-fill or clear verified_at when payment_status changes
        if (($data['payment_status'] ?? '') === 'verified') {
            if (! $this->record->verified_at) {
                $data['verified_at'] = now();
            }
        } else {
            $data['verified_at'] = null;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var HandsOnRegistration $registration */
        $registration = $this->record;

        // Rename newly uploaded payment proof to use 6-digit registration code
        if ($registration->payment_proof_path) {
            $codeNumber = substr($registration->registration_code, -6);
            $newName = $codeNumber.'.'.pathinfo($registration->payment_proof_path, PATHINFO_EXTENSION);
            $newPath = 'payment-proofs/'.$newName;
            if ($registration->payment_proof_path !== $newPath && Storage::disk('public')->exists($registration->payment_proof_path)) {
                Storage::disk('public')->move($registration->payment_proof_path, $newPath);
                $registration->update(['payment_proof_path' => $newPath]);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }
}
