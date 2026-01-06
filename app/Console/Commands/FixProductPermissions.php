<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FixProductPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:fix-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tạo và gán product permissions nếu chưa có';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Đang tạo product permissions...');

        // Tạo permissions cho quản lý sản phẩm
        $permissions = [
            'view-products',
            'create-products',
            'update-products',
            'delete-products',
            'view-product-templates',
        ];

        $created = 0;
        $existing = 0;

        foreach ($permissions as $permission) {
            $perm = Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['name' => $permission, 'guard_name' => 'web']
            );

            if ($perm->wasRecentlyCreated) {
                $created++;
                $this->line("   ✅ Đã tạo permission: {$permission}");
            } else {
                $existing++;
                $this->line("   ℹ️  Permission đã tồn tại: {$permission}");
            }
        }

        $this->info("📊 Đã tạo: {$created} permissions mới, {$existing} permissions đã tồn tại");

        // Gán permissions cho các role
        $this->info('🔄 Đang gán permissions cho các role...');

        $roles = [
            'system-admin' => $permissions,
            'manager' => $permissions,
            'team-admin' => $permissions,
            'seller' => ['view-products', 'create-products', 'update-products', 'view-product-templates'],
            'accountant' => ['view-products', 'view-product-templates'],
            'fulfill' => ['view-products', 'update-products', 'view-product-templates'],
            'viewer' => ['view-products', 'view-product-templates'],
        ];

        $assigned = 0;

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                foreach ($rolePermissions as $permission) {
                    $perm = Permission::where('name', $permission)->first();
                    if ($perm && !$role->hasPermissionTo($permission)) {
                        $role->givePermissionTo($permission);
                        $assigned++;
                    }
                }
                $this->line("   ✅ Đã gán permissions cho role: {$roleName}");
            } else {
                $this->warn("   ⚠️  Role không tồn tại: {$roleName}");
            }
        }

        $this->info("📊 Đã gán: {$assigned} permissions cho các role");

        $this->info('✅ Hoàn thành! Product permissions đã được tạo và gán.');

        return 0;
    }
}
