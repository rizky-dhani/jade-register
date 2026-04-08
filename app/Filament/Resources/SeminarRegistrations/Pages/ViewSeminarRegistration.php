<?php

namespace App\Filament\Resources\SeminarRegistrations\Pages;

use App\Filament\Resources\SeminarRegistrations\Schemas\SeminarRegistrationInfolist;
use App\Filament\Resources\SeminarRegistrations\SeminarRegistrationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewSeminarRegistration extends ViewRecord
{
    protected static string $resource = SeminarRegistrationResource::class;

    public function schema(Schema $schema): Schema
    {
        return SeminarRegistrationInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label(__('filament.actions.edit'))
                ->icon('heroicon-m-pencil-square'),
        ];
    }
}
