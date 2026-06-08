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

        // Pre-fill addon payment proof from first existing registration
        $firstAddonReg = $this->record->addonRegistrations->first();
        if ($firstAddonReg && $firstAddonReg->payment_proof_path) {
            $data['addon_payment_proof_path'] = $firstAddonReg->payment_proof_path;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var SeminarRegistration $registration */
        $registration = $this->record;

        // --- Handle add-on payment proof ---
        $addonPaymentProofPath = $this->form->getState()['addon_payment_proof_path'] ?? null;

        // Rename newly uploaded addon payment proof to use 6-digit registration code
        if ($addonPaymentProofPath) {
            $codeNumber = substr($registration->registration_code, -6);
            $newName = $codeNumber.'-addon.'.pathinfo($addonPaymentProofPath, PATHINFO_EXTENSION);
            $newPath = 'payment-proofs/'.$newName;
            if ($addonPaymentProofPath !== $newPath && Storage::disk('public')->exists($addonPaymentProofPath)) {
                Storage::disk('public')->move($addonPaymentProofPath, $newPath);
                $addonPaymentProofPath = $newPath;
            }
        }

        // --- Sync add-on registrations ---
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

        // Update payment proof on remaining existing addon registrations if a new file was uploaded
        if ($addonPaymentProofPath) {
            $registration->addonRegistrations()->update(['payment_proof_path' => $addonPaymentProofPath]);
        }

        // Get existing payment proof path for new addon registrations
        $existingProofPath = $addonPaymentProofPath
            ?? $registration->addonRegistrations()->value('payment_proof_path');

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
                    'payment_proof_path' => $existingProofPath,
                ]);
            }
        }

        // Recalculate total from remaining registrations
        $totalAmount = $registration->addonRegistrations()->sum('amount');
        $registration->update(['addons_total_amount' => $totalAmount]);

        // Rename newly uploaded (main) payment proof to use 6-digit registration code
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
