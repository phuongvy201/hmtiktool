<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class ManageSystemAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:manage 
                            {action : Action to perform (list|add|remove|transfer)}
                            {--email= : User email for add/remove actions}
                            {--new-admin= : New admin email for transfer action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage system administrators';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'list':
                $this->listSystemAdmins();
                break;
            case 'add':
                $this->addSystemAdmin();
                break;
            case 'remove':
                $this->removeSystemAdmin();
                break;
            case 'transfer':
                $this->transferSystemAdmin();
                break;
            default:
                $this->error('Invalid action. Use: list, add, remove, or transfer');
                return 1;
        }

        return 0;
    }

    private function listSystemAdmins()
    {
        $systemAdmins = User::role('system-admin')->get();

        if ($systemAdmins->isEmpty()) {
            $this->warn('No system administrators found.');
            return;
        }

        $this->info('System Administrators:');
        $this->table(
            ['ID', 'Name', 'Email', 'Team', 'Created At'],
            $systemAdmins->map(function ($admin) {
                return [
                    $admin->id,
                    $admin->name,
                    $admin->email,
                    $admin->team ? $admin->team->name : 'No Team',
                    $admin->created_at->format('Y-m-d H:i:s')
                ];
            })
        );
    }

    private function addSystemAdmin()
    {
        $email = $this->option('email');

        if (!$email) {
            $email = $this->ask('Enter user email:');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return;
        }

        if ($user->hasRole('system-admin')) {
            $this->warn("User '{$email}' is already a system administrator.");
            return;
        }

        $systemAdminRole = Role::where('name', 'system-admin')->first();

        if (!$systemAdminRole) {
            $this->error('System admin role not found. Please run migrations first.');
            return;
        }

        $user->assignRole($systemAdminRole);
        $this->info("User '{$email}' has been promoted to system administrator.");
    }

    private function removeSystemAdmin()
    {
        $email = $this->option('email');

        if (!$email) {
            $email = $this->ask('Enter user email:');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return;
        }

        if (!$user->hasRole('system-admin')) {
            $this->warn("User '{$email}' is not a system administrator.");
            return;
        }

        $systemAdminCount = User::role('system-admin')->count();

        if ($systemAdminCount <= 1) {
            $this->error('Cannot remove the last system administrator.');
            return;
        }

        if ($this->confirm("Are you sure you want to remove system admin privileges from '{$email}'?")) {
            $user->removeRole('system-admin');
            $this->info("User '{$email}' has been demoted from system administrator.");
        }
    }

    private function transferSystemAdmin()
    {
        $currentEmail = $this->option('email');
        $newAdminEmail = $this->option('new-admin');

        if (!$currentEmail) {
            $currentEmail = $this->ask('Enter current system admin email:');
        }

        if (!$newAdminEmail) {
            $newAdminEmail = $this->ask('Enter new system admin email:');
        }

        $currentAdmin = User::where('email', $currentEmail)->first();
        $newAdmin = User::where('email', $newAdminEmail)->first();

        if (!$currentAdmin) {
            $this->error("Current admin with email '{$currentEmail}' not found.");
            return;
        }

        if (!$newAdmin) {
            $this->error("New admin with email '{$newAdminEmail}' not found.");
            return;
        }

        if (!$currentAdmin->hasRole('system-admin')) {
            $this->error("User '{$currentEmail}' is not a system administrator.");
            return;
        }

        if ($newAdmin->hasRole('system-admin')) {
            $this->warn("User '{$newAdminEmail}' is already a system administrator.");
            return;
        }

        if ($this->confirm("Transfer system admin privileges from '{$currentEmail}' to '{$newAdminEmail}'?")) {
            $currentAdmin->removeRole('system-admin');
            $newAdmin->assignRole('system-admin');
            $this->info("System admin privileges transferred successfully.");
        }
    }
}
