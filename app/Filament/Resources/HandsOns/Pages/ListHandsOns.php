<?php

namespace App\Filament\Resources\HandsOns\Pages;

use App\Filament\Resources\HandsOns\HandsOnResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHandsOns extends ListRecords
{
    protected static string $resource = HandsOnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
