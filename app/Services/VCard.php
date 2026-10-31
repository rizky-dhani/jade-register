<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Writes vCard 3.0 files, the format phone address books import.
 */
class VCard
{
    /**
     * @param  iterable<array{name: string, phone: string, email?: string|null}>  $contacts
     */
    public static function build(iterable $contacts): string
    {
        $cards = '';

        foreach ($contacts as $contact) {
            $cards .= self::card($contact);
        }

        return $cards;
    }

    /**
     * @param  iterable<array{name: string, phone: string, email?: string|null}>  $contacts
     */
    public static function download(iterable $contacts, string $filename): StreamedResponse
    {
        $body = self::build($contacts);

        return response()->streamDownload(
            function () use ($body): void {
                echo $body;
            },
            $filename,
            ['Content-Type' => 'text/vcard; charset=utf-8'],
        );
    }

    /**
     * Drop contacts without a phone number and collapse duplicate numbers, so one
     * participant registered for several sessions is only saved once.
     *
     * @param  iterable<array{name: string, phone: string, email?: string|null}>  $contacts
     * @return list<array{name: string, phone: string, email?: string|null}>
     */
    public static function dedupe(iterable $contacts): array
    {
        $unique = [];

        foreach ($contacts as $contact) {
            $key = self::phoneKey($contact['phone']);

            if ($key === '' || isset($unique[$key])) {
                continue;
            }

            $unique[$key] = $contact;
        }

        return array_values($unique);
    }

    public static function phoneKey(?string $phone): string
    {
        return preg_replace('/\D+/', '', (string) $phone) ?? '';
    }

    /**
     * @param  array{name: string, phone: string, email?: string|null}  $contact
     */
    protected static function card(array $contact): string
    {
        $name = self::escape($contact['name']);

        $lines = [
            'BEGIN:VCARD',
            'VERSION:3.0',
            'N:;'.$name.';;;',
            'FN:'.$name,
            'TEL;TYPE=CELL:'.self::escape($contact['phone']),
        ];

        if (filled($contact['email'] ?? null)) {
            $lines[] = 'EMAIL;TYPE=INTERNET:'.self::escape((string) $contact['email']);
        }

        $lines[] = 'END:VCARD';

        return implode("\r\n", $lines)."\r\n";
    }

    protected static function escape(string $value): string
    {
        return str_replace(
            ['\\', ';', ',', "\r\n", "\n", "\r"],
            ['\\\\', '\;', '\,', '\n', '\n', '\n'],
            $value,
        );
    }
}
