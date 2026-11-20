<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Tài khoản của bạn đã bị tạm khóa. Vui lòng liên hệ Quản trị viên.',
            ]);
        }

        // Tương thích cả cột is_admin cũ
        $currentRole = $user->is_admin ? 'admin' : ($user->role ?? 'customer');

        if (! in_array($currentRole, $roles, true)) {
            abort(403, 'Bạn không có quyền truy cập khu vực này.');
        }

        return $next($request);
    }
}