<?php

namespace App\Filament\Resources\SeminarPackages\Pages;

use App\Filament\Resources\SeminarPackages\SeminarPackageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSeminarPackage extends EditRecord
{
    protected static string $resource = SeminarPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
