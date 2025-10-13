<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeamAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and is a team admin (but not system admin)
        if (auth()->check() && auth()->user()->hasRole('team-admin') && !auth()->user()->hasRole('system-admin')) {
            // For team admin, restrict certain actions
            $action = $request->route()->getActionMethod();

            // Team admin cannot create new teams
            if ($action === 'create' || $action === 'store') {
                if ($request->route()->getName() === 'teams.create' || $request->route()->getName() === 'teams.store') {
                    abort(403, 'Team Admin không thể tạo team mới.');
                }
            }

            // Team admin cannot create/edit/delete roles
            if (in_array($action, ['create', 'store', 'edit', 'update', 'destroy'])) {
                if (str_contains($request->route()->getName(), 'roles.')) {
                    abort(403, 'Team Admin không thể quản lý vai trò.');
                }
            }
        }

        return $next($request);
    }
}
