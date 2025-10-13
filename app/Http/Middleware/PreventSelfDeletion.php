<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PreventSelfDeletion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Chỉ áp dụng cho route destroy user
        if ($request->route()->getName() === 'users.destroy') {
            $user = $request->route('user');

            // Ngăn xóa chính mình
            if ($user && $user->id === Auth::id()) {
                return redirect()->back()->with('error', 'Bạn không thể xóa chính mình. Vui lòng liên hệ admin khác để thực hiện thao tác này.');
            }
        }

        return $next($request);
    }
}
