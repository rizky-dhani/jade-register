<?php

namespace App\Filament\Resources\SeminarRegistrations\Pages;

use App\Filament\Resources\SeminarRegistrations\SeminarRegistrationResource;
use App\Jobs\CompleteSeminarRegistration;
use App\Models\Addon;
use App\Models\Country;
use App\Models\DigitalWorkshop;
use App\Models\DigitalWorkshopRegistration;
use App\Models\SeminarRegistration;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateSeminarRegistration extends CreateRecord
{
    /** Digital-workshop virtual field state stashed in mutateFormDataBeforeCreate(). */
    protected array $digitalWorkshopPayload = [];

    protected static string $resource = SeminarRegistrationResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('filament.notifications.created_title'))
            ->body(__('filament.notifications.created_body'));
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate registration code
        $data['registration_code'] = SeminarRegistration::generateRegistrationCode();

        // Determine language based on selected country (Indonesia = id, others = en)
        $country = Country::find($data['country_id'] ?? null);
        $data['language'] = $country?->is_indonesia ? 'id' : 'en';

        // Remove virtual fields not present on the model
        unset($data['addon_ids'], $data['addon_payment_proof_path'], $data['wants_digital_workshop'], $data['digital_workshop_id']);

        // Auto-verify digital workshop payment if proof file is uploaded
        $digitalWorkshopProof = $data['digital_workshop_payment_proof_path'] ?? null;
        unset($data['digital_workshop_payment_proof_path']);

        // Stash for afterCreate()
        $this->digitalWorkshopPayload = [
            'digital_workshop_id' => $data['digital_workshop_id'] ?? null,
            'digital_workshop_payment_proof_path' => $digitalWorkshopProof,
        ];
        if (isset($data['wants_digital_workshop'])) {
            unset($data['wants_digital_workshop']);
        }

        // Auto-verify payment if proof file is uploaded
        if (! empty($data['payment_proof_path'])) {
            $data['payment_status'] = 'verified';
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var SeminarRegistration $registration */
        $registration = $this->record;

        // Create the bundled Digital Workshop registration when requested.
        $state = $this->form->getState();
        if (($state['wants_digital_workshop'] ?? false) === true
            && filled($state['digital_workshop_id'] ?? null)) {
            $proofPath = $this->digitalWorkshopPayload['digital_workshop_payment_proof_path'] ?? null;
            $workshop = DigitalWorkshop::find($state['digital_workshop_id']);

            $registration->digitalWorkshopRegistrations()->firstOrCreate(
                ['digital_workshop_id' => $workshop?->id],
                [
                    'registration_code' => DigitalWorkshopRegistration::generateRegistrationCode(),
                    'qr_token' => Str::uuid()->toString(),
                    'registration_type' => 'bundled',
                    'amount' => $workshop?->bundle_price ?? $workshop?->price ?? 0,
                    'email' => $registration->email,
                    'name' => $registration->name_license,
                    'name_license' => $registration->name_license,
                    'nik' => $registration->nik,
                    'pdgi_branch' => $registration->pdgi_branch,
                    'phone' => $registration->phone,
                    'kompetensi' => $registration->kompetensi,
                    'status' => 'registered',
                    'country_id' => $registration->country_id,
                    'payment_status' => filled($proofPath) ? 'verified' : 'pending',
                    'payment_method' => $registration->payment_method ?? 'bank_transfer',
                    'payment_proof_path' => $proofPath,
                    'language' => $registration->language,
                ]
            );
        }
        // Rename payment proof to use 6-digit registration code
        if ($registration->payment_proof_path) {
            $codeNumber = substr($registration->registration_code, -6);
            $newName = $codeNumber.'.'.pathinfo($registration->payment_proof_path, PATHINFO_EXTENSION);
            $newPath = 'payment-proofs/'.$newName;
            if (Storage::disk('public')->exists($registration->payment_proof_path)) {
                Storage::disk('public')->move($registration->payment_proof_path, $newPath);
                $registration->update(['payment_proof_path' => $newPath]);
            }
        }

        // Save add-on registrations
        $addonIds = $this->form->getState()['addon_ids'] ?? [];
        if (! empty($addonIds)) {
            $addonPaymentProofPath = $this->form->getState()['addon_payment_proof_path'] ?? null;

            // Rename addon payment proof to use 6-digit registration code
            if ($addonPaymentProofPath) {
                $codeNumber = substr($registration->registration_code, -6);
                $newName = $codeNumber.'-addon.'.pathinfo($addonPaymentProofPath, PATHINFO_EXTENSION);
                $newPath = 'payment-proofs/'.$newName;
                if (Storage::disk('public')->exists($addonPaymentProofPath)) {
                    Storage::disk('public')->move($addonPaymentProofPath, $newPath);
                    $addonPaymentProofPath = $newPath;
                }
            }

            $addons = Addon::whereIn('id', $addonIds)->get();
            $totalAmount = 0;
            foreach ($addons as $addon) {
                $registration->addonRegistrations()->create([
                    'addon_id' => $addon->id,
                    'amount' => $addon->price,
                    'currency' => $addon->currency,
                    'payment_status' => 'pending',
                    'payment_proof_path' => $addonPaymentProofPath,
                ]);
                $totalAmount += $addon->price;
            }
            $registration->update(['addons_total_amount' => $totalAmount]);
        }

        // Generate QR token and send the confirmation email off the request via the
        // same job the public flow uses, so an SMTP outage cannot block this page.
        CompleteSeminarRegistration::dispatch($registration);
    }

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }
}
