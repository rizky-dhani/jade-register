<?php

namespace App\Filament\Resources\DigitalWorkshopRegistrations\Pages;

use App\Filament\Resources\DigitalWorkshopRegistrations\DigitalWorkshopRegistrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDigitalWorkshopRegistrations extends ListRecords
{
    protected static string $resource = DigitalWorkshopRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
