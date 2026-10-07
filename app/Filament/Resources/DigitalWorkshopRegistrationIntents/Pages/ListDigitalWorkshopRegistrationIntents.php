<?php

namespace App\Filament\Resources\DigitalWorkshopRegistrationIntents\Pages;

use App\Filament\Resources\DigitalWorkshopRegistrationIntents\DigitalWorkshopRegistrationIntentResource;
use Filament\Resources\Pages\ListRecords;

class ListDigitalWorkshopRegistrationIntents extends ListRecords
{
    protected static string $resource = DigitalWorkshopRegistrationIntentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
