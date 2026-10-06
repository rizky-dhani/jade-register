<?php

namespace App\Filament\Resources\DigitalWorkshopRegistrations\Pages;

use App\Filament\Resources\DigitalWorkshopRegistrations\DigitalWorkshopRegistrationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDigitalWorkshopRegistration extends EditRecord
{
    protected static string $resource = DigitalWorkshopRegistrationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
