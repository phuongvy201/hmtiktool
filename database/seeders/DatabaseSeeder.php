<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Team;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run role and permission seeder first
        $this->call([
            RolePermissionSeeder::class,
            ProductPermissionsSeeder::class, // Product permissions (view-products, create-products, update-products, etc.)
            TeamSeeder::class,
            SystemSettingSeeder::class,
            ServicePackageSeeder::class,
            BackupPermissionSeeder::class,
        ]);

        // Create a default team
        $defaultTeam = Team::create([
            'name' => 'Default Team',
            'slug' => 'default-team',
            'description' => 'Default team for system users',
            'status' => 'active',
        ]);

        // Create system users
        $this->createSystemUsers($defaultTeam);

        // Create team users
        $this->createTeamUsers($defaultTeam);
    }

    private function createSystemUsers($defaultTeam)
    {
        // Create a system admin user
        $systemAdmin = User::factory()->create([
            'name' => 'System Admin',
            'email' => 'admin@system.com',
            'password' => bcrypt('password'),
            'is_system_user' => true,
            'team_id' => $defaultTeam->id,
        ]);
        $systemAdmin->assignRole('system-admin');

        // Create a system accountant user
        $systemAccountant = User::factory()->create([
            'name' => 'System Accountant',
            'email' => 'accountant@system.com',
            'password' => bcrypt('password'),
            'is_system_user' => true,
            'team_id' => $defaultTeam->id,
        ]);
        $systemAccountant->assignRole('system-accountant');

        // Create a system fulfill manager user
        $systemFulfillManager = User::factory()->create([
            'name' => 'System Fulfill Manager',
            'email' => 'fulfill@system.com',
            'password' => bcrypt('password'),
            'is_system_user' => true,
            'team_id' => $defaultTeam->id,
        ]);
        $systemFulfillManager->assignRole('system-fulfill-manager');
    }

    private function createTeamUsers($defaultTeam)
    {
        // Create a team admin user
        $teamAdmin = User::factory()->create([
            'name' => 'Team Admin',
            'email' => 'team-admin@example.com',
            'password' => bcrypt('password'),
            'is_system_user' => false,
            'team_id' => $defaultTeam->id,
        ]);
        $teamAdmin->assignRole('team-admin');

        // Create a seller user
        $seller = User::factory()->create([
            'name' => 'Seller User',
            'email' => 'seller@example.com',
            'password' => bcrypt('password'),
            'is_system_user' => false,
            'team_id' => $defaultTeam->id,
        ]);
        $seller->assignRole('seller');

        // Create a fulfill user
        $fulfill = User::factory()->create([
            'name' => 'Fulfill User',
            'email' => 'fulfill@example.com',
            'password' => bcrypt('password'),
            'is_system_user' => false,
            'team_id' => $defaultTeam->id,
        ]);
        $fulfill->assignRole('fulfill');

        // Create an accountant user
        $accountant = User::factory()->create([
            'name' => 'Accountant User',
            'email' => 'accountant@example.com',
            'password' => bcrypt('password'),
            'is_system_user' => false,
            'team_id' => $defaultTeam->id,
        ]);
        $accountant->assignRole('accountant');

        // Create a viewer user
        $viewer = User::factory()->create([
            'name' => 'Viewer User',
            'email' => 'viewer@example.com',
            'password' => bcrypt('password'),
            'is_system_user' => false,
            'team_id' => $defaultTeam->id,
        ]);
        $viewer->assignRole('viewer');
    }
}
