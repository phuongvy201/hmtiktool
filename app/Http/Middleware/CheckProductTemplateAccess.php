<?php

namespace App\Http\Middleware;

use App\Models\ProductTemplate;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckProductTemplateAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // System Admin: Không có quyền truy cập Product Templates
        if ($user->hasRole('system-admin')) {
            abort(403, 'System Admin không có quyền truy cập Product Templates. Chỉ có thể quản lý hệ thống chung.');
        }

        // Kiểm tra nếu có product_template parameter
        if ($request->route('product_template')) {
            $template = $request->route('product_template');

            // Kiểm tra user có thuộc team của template không
            if ($user->team_id !== $template->team_id) {
                abort(403, 'Bạn không có quyền truy cập template này.');
            }

            // Team Admin: Có quyền truy cập tất cả template trong team
            if ($user->hasRole('team-admin')) {
                return $next($request);
            }

            // Seller: Chỉ truy cập được template do chính mình tạo
            if ($user->id !== $template->user_id) {
                abort(403, 'Bạn chỉ có thể truy cập template do chính mình tạo.');
            }
        }

        return $next($request);
    }
}
