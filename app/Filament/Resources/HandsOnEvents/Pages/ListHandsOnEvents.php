<?php

namespace App\Filament\Resources\HandsOnEvents\Pages;

use App\Filament\Resources\HandsOnEvents\HandsOnEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHandsOnEvents extends ListRecords
{
    protected static string $resource = HandsOnEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
