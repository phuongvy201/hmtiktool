<?php

namespace App\Policies;

use App\Models\ProductTemplate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductTemplatePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // System Admin: Không có quyền xem template của các team
        if ($user->hasRole('system-admin')) {
            return false;
        }

        // Team Admin & Seller: Có quyền xem template của team mình
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProductTemplate $productTemplate): bool
    {
        // Debug logging
        \Illuminate\Support\Facades\Log::info('ProductTemplatePolicy::view called', [
            'user_id' => $user->id,
            'user_team_id' => $user->team_id,
            'template_id' => $productTemplate->id,
            'template_user_id' => $productTemplate->user_id,
            'template_team_id' => $productTemplate->team_id,
            'user_roles' => $user->roles->pluck('name')->toArray()
        ]);

        // System Admin: Có quyền xem tất cả templates
        if ($user->hasRole('system-admin')) {
            \Illuminate\Support\Facades\Log::info('User is system-admin, allowing access');
            return true;
        }

        // Kiểm tra user có thuộc team của template không
        if ($user->team_id !== $productTemplate->team_id) {
            \Illuminate\Support\Facades\Log::info('Team mismatch, denying access', [
                'user_team_id' => $user->team_id,
                'template_team_id' => $productTemplate->team_id
            ]);
            return false;
        }

        // Team Admin: Có quyền xem tất cả template trong team
        if ($user->hasRole('team-admin')) {
            \Illuminate\Support\Facades\Log::info('User is team-admin, allowing access');
            return true;
        }

        // Seller: Chỉ xem được template do chính mình tạo
        $isOwner = $user->id === $productTemplate->user_id;
        \Illuminate\Support\Facades\Log::info('Checking if user is owner', [
            'user_id' => $user->id,
            'template_user_id' => $productTemplate->user_id,
            'is_owner' => $isOwner
        ]);

        return $isOwner;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // System Admin: Có quyền tạo template
        if ($user->hasRole('system-admin')) {
            return true;
        }

        // Team Admin & Seller: Có quyền tạo template trong team mình
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProductTemplate $productTemplate): bool
    {
        // System Admin: Có quyền chỉnh sửa tất cả templates
        if ($user->hasRole('system-admin')) {
            return true;
        }

        // Kiểm tra user có thuộc team của template không
        if ($user->team_id !== $productTemplate->team_id) {
            return false;
        }

        // Team Admin: Có quyền chỉnh sửa tất cả template trong team
        if ($user->hasRole('team-admin')) {
            return true;
        }

        // Seller: Chỉ chỉnh sửa được template do chính mình tạo
        return $user->id === $productTemplate->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProductTemplate $productTemplate): bool
    {
        // System Admin: Có quyền xóa tất cả templates
        if ($user->hasRole('system-admin')) {
            return true;
        }

        // Kiểm tra user có thuộc team của template không
        if ($user->team_id !== $productTemplate->team_id) {
            return false;
        }

        // Team Admin: Có quyền xóa tất cả template trong team
        if ($user->hasRole('team-admin')) {
            return true;
        }

        // Seller: Chỉ xóa được template do chính mình tạo
        return $user->id === $productTemplate->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProductTemplate $productTemplate): bool
    {
        // System Admin: Không có quyền restore template
        if ($user->hasRole('system-admin')) {
            return false;
        }

        // Kiểm tra user có thuộc team của template không
        if ($user->team_id !== $productTemplate->team_id) {
            return false;
        }

        // Team Admin: Có quyền restore tất cả template trong team
        if ($user->hasRole('team-admin')) {
            return true;
        }

        // Seller: Chỉ restore được template do chính mình tạo
        return $user->id === $productTemplate->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProductTemplate $productTemplate): bool
    {
        // System Admin: Không có quyền force delete template
        if ($user->hasRole('system-admin')) {
            return false;
        }

        // Kiểm tra user có thuộc team của template không
        if ($user->team_id !== $productTemplate->team_id) {
            return false;
        }

        // Team Admin: Có quyền force delete tất cả template trong team
        if ($user->hasRole('team-admin')) {
            return true;
        }

        // Seller: Chỉ force delete được template do chính mình tạo
        return $user->id === $productTemplate->user_id;
    }

    /**
     * Get templates that user can view
     */
    public function getViewableTemplates(User $user)
    {
        // System Admin: Không có quyền xem template nào
        if ($user->hasRole('system-admin')) {
            return collect();
        }

        // Team Admin: Xem tất cả template trong team
        if ($user->hasRole('team-admin')) {
            return ProductTemplate::where('team_id', $user->team_id);
        }

        // Seller: Chỉ xem template do chính mình tạo
        return ProductTemplate::where('team_id', $user->team_id)
            ->where('user_id', $user->id);
    }
}
