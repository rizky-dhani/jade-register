<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAttendance extends EditRecord
{
    protected static string $resource = AttendanceResource::class;

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
            ->title(__('filament.notifications.attendance_updated_title'))
            ->body(__('filament.notifications.attendance_updated_body'));
    }

    protected function getDeletedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('filament.notifications.attendance_deleted_title'))
            ->body(__('filament.notifications.attendance_deleted_body'));
    }
}
