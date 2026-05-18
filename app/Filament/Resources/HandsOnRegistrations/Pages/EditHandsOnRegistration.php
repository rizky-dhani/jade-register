<?php

namespace App\Filament\Resources\HandsOnRegistrations\Pages;

use App\Filament\Resources\HandsOnRegistrations\HandsOnRegistrationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

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
}
