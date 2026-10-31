<?php

namespace App\Filament\Resources\HandsOnRegistrations\Actions;

use App\Models\HandsOnRegistration;
use App\Services\PhoneNumber;
use App\Services\VCard;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SaveContactAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'saveContact';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament.contacts.save_contact'))
            ->icon(Heroicon::OutlinedUserPlus)
            ->color('gray')
            ->visible(fn (HandsOnRegistration $record): bool => static::contactFor($record) !== null)
            ->action(function (HandsOnRegistration $record): ?StreamedResponse {
                $contact = static::contactFor($record);

                if ($contact === null) {
                    Notification::make()
                        ->title(__('filament.contacts.phone_missing'))
                        ->warning()
                        ->send();

                    return null;
                }

                return VCard::download([$contact], static::filename($contact['name']));
            });
    }

    /**
     * Hands-on registrations often carry no personal data of their own and inherit
     * the participant identity from the linked seminar registration.
     *
     * @return array{name: string, phone: string, email: string|null}|null
     */
    public static function contactFor(HandsOnRegistration $record): ?array
    {
        $seminarRegistration = $record->seminarRegistration;

        // The phone may live on either row, so the country must follow the row it came from.
        $phone = $seminarRegistration?->phone ?: $record->phone;
        $country = filled($seminarRegistration?->phone)
            ? $seminarRegistration->country
            : $record->country;

        $normalized = PhoneNumber::normalize($phone, $country);

        if ($normalized === null) {
            return null;
        }

        $name = $seminarRegistration?->name_license
            ?: $record->name_license
            ?: $record->name
            ?: $record->registration_code;

        $email = $seminarRegistration?->email ?: $record->email;

        return [
            'name' => (string) $name,
            'phone' => $normalized,
            'email' => filled($email) ? (string) $email : null,
        ];
    }

    /**
     * @param  iterable<HandsOnRegistration>  $records
     * @return list<array{name: string, phone: string, email: string|null}>
     */
    public static function contactsFor(iterable $records): array
    {
        if ($records instanceof EloquentCollection) {
            $records->loadMissing('country', 'seminarRegistration.country');
        }

        $contacts = [];

        foreach ($records as $record) {
            $contact = static::contactFor($record);

            if ($contact !== null) {
                $contacts[] = $contact;
            }
        }

        return VCard::dedupe($contacts);
    }

    public static function filename(string $name): string
    {
        $slug = Str::slug($name);

        return ($slug === '' ? 'contact' : $slug).'.vcf';
    }
}
