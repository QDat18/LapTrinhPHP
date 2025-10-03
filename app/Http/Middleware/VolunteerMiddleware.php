<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VolunteerMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để tiếp tục');
        }

        if (Auth::user()->user_type !== 'Volunteer') {
            abort(403, 'Chỉ tình nguyện viên mới có quyền truy cập trang này');
        }

        return $next($request);
    }
}