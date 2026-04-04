<?php

namespace App\Filament\Resources\SeminarRegistrations\Pages;

use App\Filament\Resources\SeminarRegistrations\SeminarRegistrationResource;
use App\Models\SeminarRegistration;
use App\Services\RegistrationService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSeminarRegistration extends CreateRecord
{
    protected static string $resource = SeminarRegistrationResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('filament.notifications.created_title'))
            ->body(__('filament.notifications.created_body'));
    }

    protected function afterCreate(): void
    {
        /** @var SeminarRegistration $registration */
        $registration = $this->record;

        // Send confirmation email (same as Livewire)
        $registrationService = app(RegistrationService::class);
        $registrationService->sendSeminarSubmissionConfirmation($registration);
    }

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }
}
