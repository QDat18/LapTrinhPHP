<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizationMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để tiếp tục');
        }

        if (Auth::user()->user_type !== 'Organization') {
            abort(403, 'Chỉ tổ chức mới có quyền truy cập trang này');
        }

        // Check if organization is verified
        $organization = Auth::user()->organization;
        if ($organization && $organization->verification_status !== 'Verified') {
            return redirect()->route('organization.profile.show')
                ->with('warning', 'Tài khoản tổ chức của bạn đang chờ xác minh. Một số chức năng bị giới hạn.');
        }

        return $next($request);
    }
}