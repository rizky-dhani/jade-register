<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['password'])) {
            $data['password'] = Hash::make('Jade2026!');
        }

        // Mark email as verified when creating from admin panel
        $data['email_verified_at'] = now();

        return $data;
    }
}
