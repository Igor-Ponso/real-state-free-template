<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'manage-properties',
            'manage-users',
            'manage-inquiries',
            'view-dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        Role::firstOrCreate(['name' => 'admin'])
            ->givePermissionTo($permissions);

        Role::firstOrCreate(['name' => 'agent'])
            ->givePermissionTo(['manage-properties', 'manage-inquiries', 'view-dashboard']);

        Role::firstOrCreate(['name' => 'client']);
    }
}
