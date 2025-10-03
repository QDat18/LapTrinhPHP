<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        if (auth()->check()) {
            return $this->redirectBasedOnRole();
        }

        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->only('email', 'remember'));
        }

        // Check if user exists
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()->back()
                ->with('error', 'No account found with this email address.')
                ->withInput($request->only('email'));
        }

        // Check if account is active
        if (!$user->is_active) {
            return redirect()->back()
                ->with('error', 'Your account has been suspended. Please contact support.')
                ->withInput($request->only('email'));
        }

        // Check if email is verified
        if (!$user->email_verified_at) {
            return redirect()->route('verification.notice', ['user_id' => $user->user_id])
                ->with('error', 'Please verify your email address first.');
        }

        // Attempt login
        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Update last login
            $user->last_login_at = now();
            $user->login_count = ($user->login_count ?? 0) + 1;
            $user->save();

            return $this->redirectBasedOnRole();
        }

        return redirect()->back()
            ->with('error', 'The provided credentials do not match our records.')
            ->withInput($request->only('email'));
    }

    /**
     * Redirect based on user role
     */
    private function redirectBasedOnRole()
    {
        $user = auth()->user();

        switch ($user->user_type) {
            case 'Admin':
                return redirect()->route('admin.dashboard');
            case 'Organization':
                return redirect()->route('organization.dashboard');
            case 'Volunteer':
            default:
                return redirect()->route('volunteer.dashboard');
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'You have been logged out successfully.');
    }
}
