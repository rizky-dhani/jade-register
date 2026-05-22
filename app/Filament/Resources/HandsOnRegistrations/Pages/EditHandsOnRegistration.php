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

    protected function mutateFormDataBeforeSave(array $data): array
    {
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
}
