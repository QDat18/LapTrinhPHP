<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VolunteerProfile;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthController extends Controller{
        public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'user_type' => 'required|in:Volunteer,Organization',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|string|email|max:100|unique:users',
            'phone' => 'required|string|max:15|unique:users',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:Male,Female,Other',
            'city' => 'required|string|max:50',
            'district' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'accepted',
        ], [
            'user_type.required' => 'Please select account type',
            'first_name.required' => 'First name is required',
            'last_name.required' => 'Last name is required',
            'email.required' => 'Email address is required',
            'email.email' => 'Please enter a valid email address',
            'email.unique' => 'This email is already registered',
            'phone.required' => 'Phone number is required',
            'phone.unique' => 'This phone number is already registered',
            'city.required' => 'Please select your city',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Password confirmation does not match',
            'terms.accepted' => 'You must agree to the terms and conditions',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Create user
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'city' => $request->city,
                'district' => $request->district,
                'address' => $request->address,
                'user_type' => $request->user_type,
                'is_active' => true,
                'is_verified' => false,
            ]);

            // Create profile based on user type
            if ($request->user_type === 'Volunteer') {
                VolunteerProfile::create([
                    'user_id' => $user->user_id,
                    'total_volunteer_hours' => 0,
                    'volunteer_rating' => 0.00,
                ]);
            } elseif ($request->user_type === 'Organization') {
                Organization::create([
                    'user_id' => $user->user_id,
                    'organization_name' => $request->first_name . ' ' . $request->last_name,
                    'verification_status' => 'Pending',
                    'volunteer_count' => 0,
                    'rating' => 0.00,
                    'total_opportunities' => 0,
                ]);
            }

            // Auto login after registration
            Auth::login($user);

            return redirect()->route('home')->with('success', 'Registration successful! Welcome to Volunteer Connect.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Registration failed. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Email address is required',
            'email.email' => 'Please enter a valid email address',
            'password.required' => 'Password is required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->only('email', 'remember'));
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        // Check if user exists and is active
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()->back()
                ->with('error', 'No account found with this email address.')
                ->withInput($request->only('email', 'remember'));
        }

        if (!$user->is_active) {
            return redirect()->back()
                ->with('error', 'Your account has been deactivated. Please contact support.')
                ->withInput($request->only('email', 'remember'));
        }

        // Attempt login
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Update last login
            $user->update(['last_login_at' => now()]);

            return redirect()->intended(route('home'))
                ->with('success', 'Welcome back, ' . $user->first_name . '!');
        }

        return redirect()->back()
            ->with('error', 'Invalid email or password.')
            ->withInput($request->only('email', 'remember'));
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link
     */
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Email address is required',
            'email.email' => 'Please enter a valid email address',
            'email.exists' => 'We could not find an account with this email address',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Show reset password form
     */
    public function showResetPasswordForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Handle password reset
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ], [
            'email.required' => 'Email address is required',
            'email.email' => 'Please enter a valid email address',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Password confirmation does not match',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        // Implement Google OAuth redirect
        // You'll need to install Laravel Socialite package
        // return Socialite::driver('google')->redirect();
        
        return redirect()->route('login')
            ->with('error', 'Google login is not configured yet.');
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback()
    {
        // Implement Google OAuth callback
        // You'll need to install Laravel Socialite package
        
        return redirect()->route('login')
            ->with('error', 'Google login is not configured yet.');
    }

    /**
     * Redirect to Facebook OAuth
     */
    public function redirectToFacebook()
    {
        // Implement Facebook OAuth redirect
        // You'll need to install Laravel Socialite package
        // return Socialite::driver('facebook')->redirect();
        
        return redirect()->route('login')
            ->with('error', 'Facebook login is not configured yet.');
    }

    /**
     * Handle Facebook OAuth callback
     */
    public function handleFacebookCallback()
    {
        // Implement Facebook OAuth callback
        // You'll need to install Laravel Socialite package
        
        return redirect()->route('login')
            ->with('error', 'Facebook login is not configured yet.');
    }

}