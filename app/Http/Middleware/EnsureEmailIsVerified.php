<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->email_verified_at) {
            return redirect()->route('verification.notice', ['user_id' => $request->user()->user_id])
                ->with('error', 'Please verify your email address.');
        }

        return $next($request);
    }
}
