<?php

namespace App\Filament\Resources\Seminars\Pages;

use App\Filament\Resources\Seminars\SeminarResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSeminar extends EditRecord
{
    protected static string $resource = SeminarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('filament.notifications.seminar_updated_title'))
            ->body(__('filament.notifications.seminar_updated_body'));
    }

    protected function getDeletedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('filament.notifications.seminar_deleted_title'))
            ->body(__('filament.notifications.seminar_deleted_body'));
    }
}
