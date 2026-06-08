<?php

namespace App\Filament\Resources\SeminarRegistrations\Pages;

use App\Filament\Resources\SeminarRegistrations\SeminarRegistrationResource;
use App\Models\Addon;
use App\Models\SeminarRegistration;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditSeminarRegistration extends EditRecord
{
    protected static string $resource = SeminarRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('filament.notifications.updated_title'))
            ->body(__('filament.notifications.updated_body'));
    }

    protected function getDeletedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('filament.notifications.deleted_title'))
            ->body(__('filament.notifications.deleted_body'));
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['addon_ids'] = $this->record->addonRegistrations->pluck('addon_id')->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var SeminarRegistration $registration */
        $registration = $this->record;

        // Sync add-on registrations
        $addonIds = $this->form->getState()['addon_ids'] ?? [];

        if (! empty($addonIds)) {
            $addonIds = array_map('intval', $addonIds);
        }

        // Get existing addon IDs before any changes
        $existingIds = $registration->addonRegistrations()->pluck('addon_id')->toArray();

        // Remove deselected add-ons
        $registration->addonRegistrations()
            ->whereNotIn('addon_id', $addonIds)
            ->delete();

        // Add new add-ons
        $newIds = array_diff($addonIds, $existingIds);
        if (! empty($newIds)) {
            $addons = Addon::whereIn('id', $newIds)->get();
            foreach ($addons as $addon) {
                $registration->addonRegistrations()->create([
                    'addon_id' => $addon->id,
                    'amount' => $addon->price,
                    'currency' => $addon->currency,
                    'payment_status' => 'pending',
                ]);
            }
        }

        // Recalculate total from remaining registrations
        $totalAmount = $registration->addonRegistrations()->sum('amount');
        $registration->update(['addons_total_amount' => $totalAmount]);

        // Rename newly uploaded payment proof to use 6-digit registration code
        if ($registration->payment_proof_path) {
            $codeNumber = substr($registration->registration_code, -6);
            $newName = $codeNumber.'.'.pathinfo($registration->payment_proof_path, PATHINFO_EXTENSION);
            $newPath = 'payment-proofs/'.$newName;
            if ($registration->payment_proof_path !== $newPath && Storage::disk('public')->exists($registration->payment_proof_path)) {
                Storage::disk('public')->move($registration->payment_proof_path, $newPath);
                $registration->update(['payment_proof_path' => $newPath]);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }
}
