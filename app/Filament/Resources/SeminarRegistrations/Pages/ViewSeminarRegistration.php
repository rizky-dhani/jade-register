<?php

namespace App\Filament\Resources\SeminarRegistrations\Pages;

use App\Filament\Resources\SeminarRegistrations\Schemas\SeminarRegistrationInfolist;
use App\Filament\Resources\SeminarRegistrations\SeminarRegistrationResource;
use Filament\Actions\EditAction;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewSeminarRegistration extends ViewRecord
{
    protected static string $resource = SeminarRegistrationResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return SeminarRegistrationInfolist::configure($infolist);
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
