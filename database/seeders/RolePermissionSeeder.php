<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view visitors',
            'export visitors',
            'view seminar registrations',
            'verify payments',
            'reject payments',
            'export seminar registrations',
            'manage countries',
            'manage settings',
            'manage professions',
            'manage marketing sources',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

        $adminPermissions = Permission::pluck('name')->toArray();
        $adminRole->givePermissionTo($adminPermissions);

        $staffRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $staffRole->givePermissionTo([
            'view visitors',
            'export visitors',
            'view seminar registrations',
            'verify payments',
            'reject payments',
            'export seminar registrations',
        ]);
    }
}
