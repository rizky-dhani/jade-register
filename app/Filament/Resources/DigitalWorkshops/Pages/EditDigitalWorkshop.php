<?php

namespace App\Filament\Resources\DigitalWorkshops\Pages;

use App\Filament\Resources\DigitalWorkshops\DigitalWorkshopResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDigitalWorkshop extends EditRecord
{
    protected static string $resource = DigitalWorkshopResource::class;

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
