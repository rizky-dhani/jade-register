<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'superadmin@jakartadentalexhibitions.id'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('SuJade2026!'),
            ]
        );

        $user->assignRole('Super Admin');
    }
}
