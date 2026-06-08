<?php

namespace App\Filament\Resources\SeminarRegistrations\Pages;

use App\Filament\Resources\SeminarRegistrations\Schemas\SeminarRegistrationInfolist;
use App\Filament\Resources\SeminarRegistrations\SeminarRegistrationResource;
use App\Models\AddonRegistration;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
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

    protected function getActions(): array
    {
        return [
            Action::make('verifyAddonPayment')
                ->label(__('seminar.verify_payment'))
                ->icon('heroicon-o-check-circle')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function (array $arguments) {
                    $addonReg = AddonRegistration::findOrFail($arguments['addonRegistrationId']);
                    $addonReg->update([
                        'payment_status' => 'verified',
                        'verified_by' => auth()->id(),
                        'verified_at' => now(),
                    ]);

                    Notification::make()
                        ->title(__('filament.notifications.payment_verified_title'))
                        ->body(__('filament.notifications.addon_payment_verified_body'))
                        ->success()
                        ->send();

                    $this->dispatch('$refresh');
                }),
        ];
    }
}
