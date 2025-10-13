<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProductPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo permissions cho quản lý sản phẩm
        $permissions = [
            'view-products',
            'create-products',
            'update-products',
            'delete-products',
            'view-product-templates',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Gán permissions cho các role
        $roles = [
            'system-admin' => $permissions,
            'manager' => $permissions,
            'team-admin' => $permissions,
            'seller' => ['view-products', 'create-products', 'update-products', 'view-product-templates'],
            'accountant' => ['view-products', 'view-product-templates'],
            'fulfill' => ['view-products', 'update-products', 'view-product-templates'],
            'viewer' => ['view-products', 'view-product-templates'],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                foreach ($rolePermissions as $permission) {
                    $role->givePermissionTo($permission);
                }
            }
        }

        $this->command->info('Product permissions seeded successfully!');
    }
}
