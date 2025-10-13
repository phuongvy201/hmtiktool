<?php

namespace App\Helpers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TeamPermissionHelper
{
    /**
     * Check if current user is a team admin (but not system admin)
     */
    public static function isTeamAdmin(): bool
    {
        return Auth::user()->hasRole('team-admin') && !Auth::user()->hasRole('system-admin');
    }

    /**
     * Check if current user is a system admin
     */
    public static function isSystemAdmin(): bool
    {
        return Auth::user()->hasRole('system-admin');
    }

    /**
     * Check if current user is a manager
     */
    public static function isManager(): bool
    {
        return Auth::user()->hasRole('manager');
    }

    /**
     * Check if user can manage a specific team
     */
    public static function canManageTeam(Team $team): bool
    {
        $user = Auth::user();

        // System admin can manage all teams
        if (self::isSystemAdmin()) {
            return true;
        }

        // Manager can manage all teams
        if (self::isManager()) {
            return true;
        }

        // Team admin can only manage their own team
        if (self::isTeamAdmin()) {
            return $user->team_id === $team->id;
        }

        return false;
    }

    /**
     * Filter teams based on user permissions
     */
    public static function filterTeams(Builder $query): Builder
    {
        $user = Auth::user();

        // System admin and manager can see all teams
        if (self::isSystemAdmin() || self::isManager()) {
            return $query;
        }

        // Team admin can only see their own team
        if (self::isTeamAdmin()) {
            return $query->where('id', $user->team_id);
        }

        // Regular users can only see their own team
        if ($user->team_id) {
            return $query->where('id', $user->team_id);
        }

        // Users without team can't see any teams
        return $query->whereRaw('1 = 0');
    }

    /**
     * Get team level users (non-system users)
     */
    public static function getTeamLevelUsers()
    {
        return User::where('is_system_user', false)
            ->whereNull('team_id')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get users that can be assigned to teams
     */
    public static function getAssignableUsers()
    {
        return User::where('is_system_user', false)
            ->orderBy('name')
            ->get();
    }

    /**
     * Check if user can be assigned to a team
     */
    public static function canAssignUserToTeam(User $user, Team $team): bool
    {
        // User must be team level
        if ($user->is_system_user) {
            return false;
        }

        // User must not already be in a team
        if ($user->team_id) {
            return false;
        }

        // Check if current user has permission to manage this team
        return self::canManageTeam($team);
    }

    /**
     * Check if user can be removed from a team
     */
    public static function canRemoveUserFromTeam(User $user, Team $team): bool
    {
        // User must be in the team
        if ($user->team_id !== $team->id) {
            return false;
        }

        // Check if current user has permission to manage this team
        if (!self::canManageTeam($team)) {
            return false;
        }

        // Don't allow removing yourself
        if ($user->id === Auth::id()) {
            return false;
        }

        return true;
    }

    /**
     * Get teams that current user can manage
     */
    public static function getManageableTeams()
    {
        $user = Auth::user();

        if (self::isSystemAdmin() || self::isManager()) {
            return Team::orderBy('name')->get();
        }

        if (self::isTeamAdmin() && $user->team_id) {
            return Team::where('id', $user->team_id)->get();
        }

        return collect();
    }

    /**
     * Check if user can create teams
     */
    public static function canCreateTeams(): bool
    {
        return self::isSystemAdmin() || self::isManager();
    }

    /**
     * Check if user can delete teams
     */
    public static function canDeleteTeams(): bool
    {
        return self::isSystemAdmin();
    }

    /**
     * Check if user can edit teams
     */
    public static function canEditTeams(): bool
    {
        return self::isSystemAdmin() || self::isManager() || self::isTeamAdmin();
    }

    /**
     * Check if user can view teams
     */
    public static function canViewTeams(): bool
    {
        return self::isSystemAdmin() || self::isManager() || self::isTeamAdmin() || Auth::user()->team_id;
    }

    /**
     * Get user's team level
     */
    public static function getUserLevel(): string
    {
        $user = Auth::user();

        if ($user->is_system_user) {
            return 'system';
        }

        return 'team';
    }

    /**
     * Get user's role level
     */
    public static function getRoleLevel(): string
    {
        if (self::isSystemAdmin()) {
            return 'system-admin';
        }

        if (self::isManager()) {
            return 'manager';
        }

        if (self::isTeamAdmin()) {
            return 'team-admin';
        }

        return 'user';
    }

    /**
     * Check if current user can access team management
     */
    public static function canAccessTeamManagement(): bool
    {
        return self::canViewTeams() || self::canCreateTeams() || self::canEditTeams();
    }

    /**
     * Get team statistics for current user
     */
    public static function getTeamStatistics(): array
    {
        $user = Auth::user();
        $stats = [
            'total_teams' => 0,
            'active_teams' => 0,
            'inactive_teams' => 0,
            'suspended_teams' => 0,
            'total_members' => 0,
            'my_team' => null,
        ];

        if (self::isSystemAdmin() || self::isManager()) {
            // System admin and manager can see all teams
            $teams = Team::with('users')->get();
            $stats['total_teams'] = $teams->count();
            $stats['active_teams'] = $teams->where('status', 'active')->count();
            $stats['inactive_teams'] = $teams->where('status', 'inactive')->count();
            $stats['suspended_teams'] = $teams->where('status', 'suspended')->count();
            $stats['total_members'] = $teams->sum(function ($team) {
                return $team->users->count();
            });
        } elseif (self::isTeamAdmin() && $user->team_id) {
            // Team admin can see their own team
            $team = Team::with('users')->find($user->team_id);
            if ($team) {
                $stats['total_teams'] = 1;
                $stats['active_teams'] = $team->status === 'active' ? 1 : 0;
                $stats['inactive_teams'] = $team->status === 'inactive' ? 1 : 0;
                $stats['suspended_teams'] = $team->status === 'suspended' ? 1 : 0;
                $stats['total_members'] = $team->users->count();
                $stats['my_team'] = $team;
            }
        } elseif ($user->team_id) {
            // Regular user can see their own team
            $team = Team::with('users')->find($user->team_id);
            if ($team) {
                $stats['total_teams'] = 1;
                $stats['active_teams'] = $team->status === 'active' ? 1 : 0;
                $stats['inactive_teams'] = $team->status === 'inactive' ? 1 : 0;
                $stats['suspended_teams'] = $team->status === 'suspended' ? 1 : 0;
                $stats['total_members'] = $team->users->count();
                $stats['my_team'] = $team;
            }
        }

        return $stats;
    }
}
