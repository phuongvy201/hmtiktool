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
        // System Admin: cÃƒÂ³ quyÃ¡Â»Ân xem tÃ¡ÂºÂ¥t cÃ¡ÂºÂ£ template
        if ($user->hasRole('system-admin')) {
            return true;
        }

        // Team Admin & Seller: CÃƒÂ³ quyÃ¡Â»Ân xem template cÃ¡Â»Â§a team mÃƒÂ¬nh
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

        // System Admin: CÃƒÂ³ quyÃ¡Â»Ân xem tÃ¡ÂºÂ¥t cÃ¡ÂºÂ£ templates
        if ($user->hasRole('system-admin')) {
            \Illuminate\Support\Facades\Log::info('User is system-admin, allowing access');
            return true;
        }

        // KiÃ¡Â»Æ’m tra user cÃƒÂ³ thuÃ¡Â»â„¢c team cÃ¡Â»Â§a template khÃƒÂ´ng
        if ($user->team_id !== $productTemplate->team_id) {
            \Illuminate\Support\Facades\Log::info('Team mismatch, denying access', [
                'user_team_id' => $user->team_id,
                'template_team_id' => $productTemplate->team_id
            ]);
            return false;
        }

        // Team Admin: CÃƒÂ³ quyÃ¡Â»Ân xem tÃ¡ÂºÂ¥t cÃ¡ÂºÂ£ template trong team
        if ($user->hasRole('team-admin')) {
            \Illuminate\Support\Facades\Log::info('User is team-admin, allowing access');
            return true;
        }

        // Seller: ChÃ¡Â»â€° xem Ã„â€˜Ã†Â°Ã¡Â»Â£c template do chÃƒÂ­nh mÃƒÂ¬nh tÃ¡ÂºÂ¡o
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
        // System Admin: CÃƒÂ³ quyÃ¡Â»Ân tÃ¡ÂºÂ¡o template
        if ($user->hasRole('system-admin')) {
            return true;
        }

        // Team Admin & Seller: CÃƒÂ³ quyÃ¡Â»Ân tÃ¡ÂºÂ¡o template trong team mÃƒÂ¬nh
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProductTemplate $productTemplate): bool
    {
        // System Admin: CÃƒÂ³ quyÃ¡Â»Ân chÃ¡Â»â€°nh sÃ¡Â»Â­a tÃ¡ÂºÂ¥t cÃ¡ÂºÂ£ templates
        if ($user->hasRole('system-admin')) {
            return true;
        }

        // KiÃ¡Â»Æ’m tra user cÃƒÂ³ thuÃ¡Â»â„¢c team cÃ¡Â»Â§a template khÃƒÂ´ng
        if ($user->team_id !== $productTemplate->team_id) {
            return false;
        }

        // Team Admin: CÃƒÂ³ quyÃ¡Â»Ân chÃ¡Â»â€°nh sÃ¡Â»Â­a tÃ¡ÂºÂ¥t cÃ¡ÂºÂ£ template trong team
        if ($user->hasRole('team-admin')) {
            return true;
        }

        // Seller: ChÃ¡Â»â€° chÃ¡Â»â€°nh sÃ¡Â»Â­a Ã„â€˜Ã†Â°Ã¡Â»Â£c template do chÃƒÂ­nh mÃƒÂ¬nh tÃ¡ÂºÂ¡o
        return $user->id === $productTemplate->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProductTemplate $productTemplate): bool
    {
        // System Admin: CÃƒÂ³ quyÃ¡Â»Ân xÃƒÂ³a tÃ¡ÂºÂ¥t cÃ¡ÂºÂ£ templates
        if ($user->hasRole('system-admin')) {
            return true;
        }

        // KiÃ¡Â»Æ’m tra user cÃƒÂ³ thuÃ¡Â»â„¢c team cÃ¡Â»Â§a template khÃƒÂ´ng
        if ($user->team_id !== $productTemplate->team_id) {
            return false;
        }

        // Team Admin: CÃƒÂ³ quyÃ¡Â»Ân xÃƒÂ³a tÃ¡ÂºÂ¥t cÃ¡ÂºÂ£ template trong team
        if ($user->hasRole('team-admin')) {
            return true;
        }

        // Seller: ChÃ¡Â»â€° xÃƒÂ³a Ã„â€˜Ã†Â°Ã¡Â»Â£c template do chÃƒÂ­nh mÃƒÂ¬nh tÃ¡ÂºÂ¡o
        return $user->id === $productTemplate->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProductTemplate $productTemplate): bool
    {
        // System Admin: KhÃƒÂ´ng cÃƒÂ³ quyÃ¡Â»Ân restore template
        if ($user->hasRole('system-admin')) {
            return false;
        }

        // KiÃ¡Â»Æ’m tra user cÃƒÂ³ thuÃ¡Â»â„¢c team cÃ¡Â»Â§a template khÃƒÂ´ng
        if ($user->team_id !== $productTemplate->team_id) {
            return false;
        }

        // Team Admin: CÃƒÂ³ quyÃ¡Â»Ân restore tÃ¡ÂºÂ¥t cÃ¡ÂºÂ£ template trong team
        if ($user->hasRole('team-admin')) {
            return true;
        }

        // Seller: ChÃ¡Â»â€° restore Ã„â€˜Ã†Â°Ã¡Â»Â£c template do chÃƒÂ­nh mÃƒÂ¬nh tÃ¡ÂºÂ¡o
        return $user->id === $productTemplate->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProductTemplate $productTemplate): bool
    {
        // System Admin: KhÃƒÂ´ng cÃƒÂ³ quyÃ¡Â»Ân force delete template
        if ($user->hasRole('system-admin')) {
            return false;
        }

        // KiÃ¡Â»Æ’m tra user cÃƒÂ³ thuÃ¡Â»â„¢c team cÃ¡Â»Â§a template khÃƒÂ´ng
        if ($user->team_id !== $productTemplate->team_id) {
            return false;
        }

        // Team Admin: CÃƒÂ³ quyÃ¡Â»Ân force delete tÃ¡ÂºÂ¥t cÃ¡ÂºÂ£ template trong team
        if ($user->hasRole('team-admin')) {
            return true;
        }

        // Seller: ChÃ¡Â»â€° force delete Ã„â€˜Ã†Â°Ã¡Â»Â£c template do chÃƒÂ­nh mÃƒÂ¬nh tÃ¡ÂºÂ¡o
        return $user->id === $productTemplate->user_id;
    }

    /**
     * Get templates that user can view
     */
    public function getViewableTemplates(User $user)
    {
        // System Admin: xem tÃ¡ÂºÂ¥t cÃ¡ÂºÂ£ template trong hÃ¡Â»â€¡ thÃ¡Â»â€˜ng
        if ($user->hasRole('system-admin')) {
            return ProductTemplate::query();
        }

        // Team Admin: Xem tÃ¡ÂºÂ¥t cÃ¡ÂºÂ£ template trong team
        if ($user->hasRole('team-admin')) {
            return ProductTemplate::where('team_id', $user->team_id);
        }

        // Seller: ChÃ¡Â»â€° xem template do chÃƒÂ­nh mÃƒÂ¬nh tÃ¡ÂºÂ¡o
        return ProductTemplate::where('team_id', $user->team_id)
            ->where('user_id', $user->id);
    }
}



