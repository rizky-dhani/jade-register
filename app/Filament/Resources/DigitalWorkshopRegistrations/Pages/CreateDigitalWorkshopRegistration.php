<?php

namespace App\Filament\Resources\DigitalWorkshopRegistrations\Pages;

use App\Filament\Resources\DigitalWorkshopRegistrations\DigitalWorkshopRegistrationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDigitalWorkshopRegistration extends CreateRecord
{
    protected static string $resource = DigitalWorkshopRegistrationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
