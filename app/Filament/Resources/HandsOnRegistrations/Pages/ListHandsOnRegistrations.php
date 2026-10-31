<?php

namespace App\Filament\Resources\HandsOnRegistrations\Pages;

use App\Exports\HandsOnRegistrationExport;
use App\Filament\Resources\HandsOnRegistrations\Actions\SaveContactAction;
use App\Filament\Resources\HandsOnRegistrations\HandsOnRegistrationResource;
use App\Services\VCard;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListHandsOnRegistrations extends ListRecords
{
    protected static string $resource = HandsOnRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    return Excel::download(
                        new HandsOnRegistrationExport,
                        'hands-on-registrations_'.now()->format('d-m-Y_H-i-s').'.xlsx',
                    );
                }),
            Action::make('exportAllContacts')
                ->label(__('filament.contacts.export_contacts'))
                ->icon(Heroicon::OutlinedUsers)
                ->action(function (): ?StreamedResponse {
                    $contacts = SaveContactAction::contactsFor($this->getFilteredTableQuery()?->get() ?? collect());

                    if ($contacts === []) {
                        Notification::make()
                            ->title(__('filament.contacts.none_found'))
                            ->warning()
                            ->send();

                        return null;
                    }

                    return VCard::download($contacts, 'hands-on-contacts_'.now()->format('d-m-Y_H-i-s').'.vcf');
                }),
            CreateAction::make(),
        ];
    }
}
