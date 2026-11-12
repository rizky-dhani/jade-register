<?php

namespace App\Filament\Resources\SeminarRegistrations\Actions;

use App\Models\SeminarRegistration;
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
            ->visible(fn (SeminarRegistration $record): bool => (auth()->user()?->hasRole('Super Admin') ?? false) && static::contactFor($record) !== null)
            ->action(function (SeminarRegistration $record): ?StreamedResponse {
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
     * @return array{name: string, phone: string, email: string|null}|null
     */
    public static function contactFor(SeminarRegistration $record): ?array
    {
        $phone = PhoneNumber::normalize($record->phone, $record->country);

        if ($phone === null) {
            return null;
        }

        return [
            'name' => (string) ($record->name_license ?: $record->registration_code),
            'phone' => $phone,
            'email' => filled($record->email) ? (string) $record->email : null,
        ];
    }

    /**
     * @param  iterable<SeminarRegistration>  $records
     * @return list<array{name: string, phone: string, email: string|null}>
     */
    public static function contactsFor(iterable $records): array
    {
        if ($records instanceof EloquentCollection) {
            $records->loadMissing('country');
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
