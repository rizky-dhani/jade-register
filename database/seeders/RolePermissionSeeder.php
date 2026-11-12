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
            'view hands on registrations',
            'create hands on registrations',
            'update hands on registrations',
            'delete hands on registrations',
            'restore hands on registrations',
            'force delete hands on registrations',
            'view digital workshops',
            'create digital workshops',
            'update digital workshops',
            'delete digital workshops',
            'restore digital workshops',
            'force delete digital workshops',
            'view digital workshop registrations',
            'create digital workshop registrations',
            'update digital workshop registrations',
            'delete digital workshop registrations',
            'restore digital workshop registrations',
            'force delete digital workshop registrations',
            'view digital workshop intents',
            'update digital workshop intents',
            'delete digital workshop intents',
            'view attendances',
            'view lunch attendances',
            'check in participants',
            'check out participants',
            'create attendances',
            'update attendances',
            'delete attendances',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

        $adminPermissions = Permission::pluck('name')->toArray();
        $adminRole->syncPermissions($adminPermissions);

        $staffRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $staffRole->syncPermissions([
            'view visitors',
            'create visitors',
            'update visitors',
            'delete visitors',
            'view seminar registrations',
            'create seminar registrations',
            'update seminar registrations',
            'delete seminar registrations',
            'restore seminar registrations',
            'force delete seminar registrations',
            'verify payments',
            'reject payments',
            'view hands on registrations',
            'create hands on registrations',
            'update hands on registrations',
            'delete hands on registrations',
            'restore hands on registrations',
            'force delete hands on registrations',
            // Desk duties: the Admin role runs the attendance desk even though
            // it is not granted the general `update attendances` permission.
            'view attendances',
            'check in participants',
            'check out participants',
        ]);

        $judgeRole = Role::firstOrCreate(['name' => 'Judge', 'guard_name' => 'web']);
        $judgeRole->givePermissionTo([
            'view poster submissions',
            'evaluate poster submissions',
        ]);

        $participantRole = Role::firstOrCreate(['name' => 'Participant', 'guard_name' => 'web']);
        $participantRole->givePermissionTo([
            'view poster submissions',
            'create poster submissions',
            'update poster submissions',
        ]);
    }
}
