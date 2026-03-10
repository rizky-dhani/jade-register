<?php

namespace App\Filament\Resources\SeminarPackages\Pages;

use App\Filament\Resources\SeminarPackages\SeminarPackageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSeminarPackages extends ListRecords
{
    protected static string $resource = SeminarPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
