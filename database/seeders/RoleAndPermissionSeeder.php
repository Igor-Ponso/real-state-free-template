<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds the Spatie Permission roles and permissions for the application.
 *
 * Creates four permissions (manage-properties, manage-users, manage-inquiries,
 * view-dashboard) and three roles with escalating access:
 * - admin: all permissions
 * - agent: manage-properties, manage-inquiries, view-dashboard
 * - client: no permissions (authenticated access only)
 */
class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Create permissions and assign them to roles.
     *
     * Clears the Spatie permission cache first, then uses firstOrCreate for
     * idempotent seeding. Admin gets all four permissions, agent gets three,
     * and client gets none (role-only for authenticated user identification).
     */
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
