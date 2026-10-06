<?php

namespace App\Filament\Resources\DigitalWorkshops\Pages;

use App\Filament\Resources\DigitalWorkshops\DigitalWorkshopResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDigitalWorkshop extends CreateRecord
{
    protected static string $resource = DigitalWorkshopResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
