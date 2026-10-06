<?php

namespace App\Filament\Resources\DigitalWorkshops\Pages;

use App\Filament\Resources\DigitalWorkshops\DigitalWorkshopResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDigitalWorkshops extends ListRecords
{
    protected static string $resource = DigitalWorkshopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
