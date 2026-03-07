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
            'create visitors',
            'update visitors',
            'delete visitors',
            'restore visitors',
            'force delete visitors',
            'export visitors',
            'view seminar registrations',
            'create seminar registrations',
            'update seminar registrations',
            'delete seminar registrations',
            'restore seminar registrations',
            'force delete seminar registrations',
            'verify payments',
            'reject payments',
            'export seminar registrations',
            'manage countries',
            'manage settings',
            'manage professions',
            'manage marketing sources',
            'view roles',
            'create roles',
            'update roles',
            'delete roles',
            'restore roles',
            'force delete roles',
            'view permissions',
            'create permissions',
            'update permissions',
            'delete permissions',
            'restore permissions',
            'force delete permissions',
            'view poster submissions',
            'create poster submissions',
            'update poster submissions',
            'delete poster submissions',
            'restore poster submissions',
            'force delete poster submissions',
            'evaluate poster submissions',
            'manage poster submissions',
            'export poster submissions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

        $adminPermissions = Permission::pluck('name')->toArray();
        $adminRole->givePermissionTo($adminPermissions);

        $staffRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $staffRole->givePermissionTo([
            'view visitors',
            'create visitors',
            'update visitors',
            'delete visitors',
            'export visitors',
            'view seminar registrations',
            'create seminar registrations',
            'update seminar registrations',
            'verify payments',
            'reject payments',
            'export seminar registrations',
            'view poster submissions',
            'update poster submissions',
            'evaluate poster submissions',
            'export poster submissions',
        ]);

        $judgeRole = Role::firstOrCreate(['name' => 'poster-judge', 'guard_name' => 'web']);
        $judgeRole->givePermissionTo([
            'view poster submissions',
            'evaluate poster submissions',
        ]);

        $participantRole = Role::firstOrCreate(['name' => 'poster-participant', 'guard_name' => 'web']);
        $participantRole->givePermissionTo([
            'view poster submissions',
            'create poster submissions',
            'update poster submissions',
        ]);
    }
}
