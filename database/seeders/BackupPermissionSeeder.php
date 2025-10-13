<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BackupPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo permissions cho backup & restore
        $permissions = [
            'view-backups',
            'create-backups',
            'download-backups',
            'restore-backups',
            'delete-backups',
            'cleanup-backups',
            'export-backups',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Gán permissions cho system-admin role
        $systemAdminRole = Role::where('name', 'system-admin')->first();

        if ($systemAdminRole) {
            $systemAdminRole->givePermissionTo($permissions);
        }
    }
}
