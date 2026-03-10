<?php

namespace App\Filament\Resources\HandsOnRegistrations\Pages;

use App\Filament\Resources\HandsOnRegistrations\HandsOnRegistrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHandsOnRegistrations extends ListRecords
{
    protected static string $resource = HandsOnRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
