<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management permissions
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',

            // Role management permissions
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles',

            // Team management permissions
            'view-teams',
            'create-teams',
            'edit-teams',
            'delete-teams',

            // Financial permissions
            'view-financial-reports',
            'create-financial-reports',
            'edit-financial-reports',
            'delete-financial-reports',
            'view-accounting',
            'create-accounting',
            'edit-accounting',
            'delete-accounting',

            // Fulfillment permissions
            'view-fulfillment',
            'create-fulfillment',
            'edit-fulfillment',
            'delete-fulfillment',
            'manage-fulfillment',

            // Sales permissions
            'view-sales',
            'create-sales',
            'edit-sales',
            'delete-sales',
            'manage-sales',

            // TikTok Shop permissions
            'view-tiktok-shops',
            'create-tiktok-shops',
            'edit-tiktok-shops',
            'delete-tiktok-shops',
            'manage-tiktok-integrations',

            // Product permissions
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',
            'manage-product-templates',

            // System permissions
            'view-system-settings',
            'edit-system-settings',
            'view-logs',
            'manage-backups',
            'manage-system',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $this->createSystemAdminRole();
        $this->createSystemAccountantRole();
        $this->createSystemFulfillManagerRole();
        $this->createTeamAdminRole();
        $this->createSellerRole();
        $this->createFulfillRole();
        $this->createAccountantRole();
        $this->createViewerRole();
    }

    private function createSystemAdminRole()
    {
        $role = Role::create(['name' => 'system-admin']);

        // Assign all permissions to system admin
        $role->givePermissionTo(Permission::all());

        $this->command->info('System Admin role created with all permissions');
    }

    private function createSystemAccountantRole()
    {
        $role = Role::create(['name' => 'system-accountant']);

        // System accountant permissions
        $permissions = [
            'view-users',
            'view-teams',
            'view-financial-reports',
            'create-financial-reports',
            'edit-financial-reports',
            'delete-financial-reports',
            'view-accounting',
            'create-accounting',
            'edit-accounting',
            'delete-accounting',
            'view-sales',
            'view-fulfillment',
            'view-system-settings',
            'view-logs',
        ];

        $role->givePermissionTo($permissions);

        $this->command->info('System Accountant role created with financial management permissions');
    }

    private function createSystemFulfillManagerRole()
    {
        $role = Role::create(['name' => 'system-fulfill-manager']);

        // System fulfill manager permissions
        $permissions = [
            'view-users',
            'view-teams',
            'view-fulfillment',
            'create-fulfillment',
            'edit-fulfillment',
            'delete-fulfillment',
            'manage-fulfillment',
            'view-sales',
            'view-financial-reports',
            'view-tiktok-shops',
            'view-products',
            'manage-product-templates',
            'view-system-settings',
            'view-logs',
        ];

        $role->givePermissionTo($permissions);

        $this->command->info('System Fulfill Manager role created with fulfillment management permissions');
    }

    private function createTeamAdminRole()
    {
        $role = Role::create(['name' => 'team-admin']);

        // Team admin permissions - limited to their own team
        $permissions = [
            'view-users',           // Only users in their team
            'create-users',         // Only users in their team
            'edit-users',           // Only users in their team
            'view-roles',           // View roles (read-only)
            'view-teams',           // Only their own team
            'edit-teams',           // Only their own team
            'view-financial-reports', // Team-specific reports
            'view-accounting',      // Team-specific accounting
            'view-fulfillment',     // Team-specific fulfillment
            'create-fulfillment',   // Team-specific fulfillment
            'edit-fulfillment',     // Team-specific fulfillment
            'view-sales',           // Team-specific sales
            'create-sales',         // Team-specific sales
            'edit-sales',           // Team-specific sales
            'view-tiktok-shops',    // Team-specific TikTok shops
            'create-tiktok-shops',  // Team-specific TikTok shops
            'edit-tiktok-shops',    // Team-specific TikTok shops
            'view-products',        // Team-specific products
            'create-products',      // Team-specific products
            'edit-products',        // Team-specific products
        ];

        $role->givePermissionTo($permissions);

        $this->command->info('Team Admin role created with team management permissions');
    }

    private function createSellerRole()
    {
        $role = Role::create(['name' => 'seller']);

        // Seller permissions
        $permissions = [
            'view-users',           // View team members
            'view-teams',           // View their team
            'view-sales',
            'create-sales',
            'edit-sales',
            'manage-sales',
            'view-tiktok-shops',    // View team TikTok shops
            'view-products',
            'create-products',
            'edit-products',
            'view-financial-reports', // Sales-related reports
        ];

        $role->givePermissionTo($permissions);

        $this->command->info('Seller role created with sales management permissions');
    }

    private function createFulfillRole()
    {
        $role = Role::create(['name' => 'fulfill']);

        // Fulfill permissions
        $permissions = [
            'view-users',           // View team members
            'view-teams',           // View their team
            'view-fulfillment',
            'create-fulfillment',
            'edit-fulfillment',
            'manage-fulfillment',
            'view-sales',           // To see orders to fulfill
            'view-tiktok-shops',    // To see TikTok shop orders
            'view-products',        // To see product details
            'view-financial-reports', // Fulfillment-related reports
        ];

        $role->givePermissionTo($permissions);

        $this->command->info('Fulfill role created with fulfillment management permissions');
    }

    private function createAccountantRole()
    {
        $role = Role::create(['name' => 'accountant']);

        // Accountant permissions
        $permissions = [
            'view-users',           // View team members
            'view-teams',           // View their team
            'view-financial-reports',
            'create-financial-reports',
            'edit-financial-reports',
            'view-accounting',
            'create-accounting',
            'edit-accounting',
            'delete-accounting',
            'view-sales',           // To see sales data
            'view-fulfillment',     // To see fulfillment costs
            'view-tiktok-shops',    // To see TikTok shop financial data
        ];

        $role->givePermissionTo($permissions);

        $this->command->info('Accountant role created with accounting management permissions');
    }

    private function createViewerRole()
    {
        $role = Role::create(['name' => 'viewer']);

        // Viewer permissions (read-only)
        $permissions = [
            'view-users',           // View team members
            'view-teams',           // View their team
            'view-financial-reports',
            'view-fulfillment',
            'view-sales',
            'view-tiktok-shops',
            'view-products',
        ];

        $role->givePermissionTo($permissions);

        $this->command->info('Viewer role created with read-only permissions');
    }
}
