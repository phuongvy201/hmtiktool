<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-users');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->can('view-users');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-users');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        if (!$user->can('edit-users')) {
            return false;
        }

        // Team admin chỉ có thể edit user trong team của mình
        if ($user->hasRole('team-admin')) {
            return $model->team_id === $user->team_id;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        if (!$user->can('delete-users')) {
            return false;
        }

        // Không thể xóa chính mình
        if ($model->id === $user->id) {
            return false;
        }

        // Không thể xóa system admin cuối cùng
        if ($model->hasRole('system-admin')) {
            $systemAdminCount = User::role('system-admin')->count();
            if ($systemAdminCount <= 1) {
                return false;
            }
        }

        // Team admin chỉ có thể xóa user trong team của mình
        if ($user->hasRole('team-admin')) {
            if ($model->team_id !== $user->team_id) {
                return false;
            }

            // Không thể xóa user có role cao hơn
            if ($model->hasRole(['system-admin', 'manager'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->can('restore-users');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->can('force-delete-users');
    }
}
