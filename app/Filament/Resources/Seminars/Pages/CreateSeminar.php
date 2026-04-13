<?php

namespace App\Filament\Resources\Seminars\Pages;

use App\Filament\Resources\Seminars\SeminarResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSeminar extends CreateRecord
{
    protected static string $resource = SeminarResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('filament.notifications.seminar_created_title'))
            ->body(__('filament.notifications.seminar_created_body'));
    }
}
