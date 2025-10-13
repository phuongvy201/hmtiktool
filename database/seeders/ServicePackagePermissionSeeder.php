<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ServicePackagePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions for service packages
        $permissions = [
            'view-service-packages',
            'create-service-packages',
            'edit-service-packages',
            'delete-service-packages',
            'restore-service-packages',
            'force-delete-service-packages',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to system-admin role
        $systemAdminRole = Role::where('name', 'system-admin')->first();

        if ($systemAdminRole) {
            $systemAdminRole->givePermissionTo($permissions);
        }
    }
}
