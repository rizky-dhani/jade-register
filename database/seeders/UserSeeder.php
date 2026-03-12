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
    }
}
