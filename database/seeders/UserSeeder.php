<?php

namespace Database\Seeders;

use App\Models\SeminarRegistration;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@jakartadentalexhibitions.id'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('SuJade2026!'),
            ]
        );
        $superAdmin->assignRole('Super Admin');

        $participant = User::firstOrCreate(
            ['email' => 'participant@jade2026.test'],
            [
                'name' => 'Dr. Test Participant',
                'password' => Hash::make('Jade2026!'),
            ]
        );
        $participant->assignRole('Participant');

        SeminarRegistration::firstOrCreate(
            ['email' => 'participant@jade2026.test'],
            [
                'registration_code' => 'JDE-SEM-2026-TEST',
                'name' => 'Dr. Test Participant',
                'name_license' => 'Test Participant',
                'nik' => '1234567890123456',
                'npa' => '123456',
                'pdgi_branch' => 'PDGI Jakarta Pusat',
                'phone' => '+6281234567890',
                'country_id' => 1,
                'registration_type' => 'online',
                'pricing_tier' => 'Early Bird - Snack + Lunch',
                'amount' => 900000,
                'currency' => 'IDR',
                'payment_status' => 'verified',
                'verified_by' => $superAdmin->getKey(),
                'verified_at' => now(),
                'wants_poster_competition' => true,
                'user_id' => $participant->getKey(),
            ]
        );
    }
}
