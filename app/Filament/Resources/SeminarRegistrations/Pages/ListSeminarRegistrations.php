<?php

namespace App\Filament\Resources\SeminarRegistrations\Pages;

use App\Filament\Resources\SeminarRegistrations\SeminarRegistrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSeminarRegistrations extends ListRecords
{
    protected static string $resource = SeminarRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
