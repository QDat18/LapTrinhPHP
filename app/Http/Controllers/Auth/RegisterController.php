<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VolunteerProfile;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\VerificationEmail;
use App\Mail\OTPVerificationEmail;

class RegisterController extends Controller
{
    /**
     * Show registration form
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        // Validate basic information
        $validator = Validator::make($request->all(), [
            'user_type' => 'required|in:Volunteer,Organization',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:15|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:Male,Female,Other',
            'city' => 'required|string|max:50',
            'district' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'terms' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create user
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'country' => 'Vietnam',
            'city' => $request->city,
            'district' => $request->district,
            'address' => $request->address,
            'user_type' => $request->user_type,
            'is_active' => true,
            'is_verified' => false,
        ]);

        // Generate verification token
        $verificationToken = Str::random(64);
        $user->verification_token = $verificationToken;
        $user->save();

        // Generate OTP (6 digits)
        $otp = rand(100000, 999999);
        
        // Store OTP in session (expires in 10 minutes)
        session([
            'otp' => $otp,
            'otp_user_id' => $user->user_id,
            'otp_expires_at' => now()->addMinutes(10)
        ]);

        // Send verification email with link
        Mail::to($user->email)->send(new VerificationEmail($user, $verificationToken));

        // Send OTP email
        Mail::to($user->email)->send(new OTPVerificationEmail($user, $otp));

        // Redirect to verification page
        return redirect()->route('verification.notice', ['user_id' => $user->user_id])
            ->with('success', 'Registration successful! Please check your email for verification instructions.');
    }

    /**
     * Show verification notice
     */
    public function showVerificationNotice($userId)
    {
        $user = User::findOrFail($userId);
        
        if ($user->email_verified_at) {
            return redirect()->route('profile.complete', ['user_id' => $userId]);
        }

        return view('auth.verify-notice', compact('user'));
    }

    /**
     * Verify email via token link
     */
    public function verifyEmail($token)
    {
        $user = User::where('verification_token', $token)->first();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Invalid verification link.');
        }

        if ($user->email_verified_at) {
            return redirect()->route('login')
                ->with('info', 'Email already verified. Please login.');
        }

        // Verify email
        $user->email_verified_at = now();
        $user->verification_token = null;
        $user->save();

        return redirect()->route('verification.otp', ['user_id' => $user->user_id])
            ->with('success', 'Email verified successfully! Please enter the OTP sent to your email.');
    }

    /**
     * Show OTP verification form
     */
    public function showOTPForm($userId)
    {
        $user = User::findOrFail($userId);

        // Check if email is verified
        if (!$user->email_verified_at) {
            return redirect()->route('verification.notice', ['user_id' => $userId])
                ->with('error', 'Please verify your email first.');
        }

        // Check if phone already verified
        if ($user->phone_verified_at) {
            return redirect()->route('profile.complete', ['user_id' => $userId]);
        }

        return view('auth.verify-otp', compact('user'));
    }

    /**
     * Verify OTP
     */
    public function verifyOTP(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|numeric|digits:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::findOrFail($userId);

        // Check if OTP exists in session
        if (!session()->has('otp') || session('otp_user_id') != $userId) {
            return redirect()->back()
                ->with('error', 'OTP not found. Please request a new one.');
        }

        // Check if OTP expired
        if (now()->greaterThan(session('otp_expires_at'))) {
            session()->forget(['otp', 'otp_user_id', 'otp_expires_at']);
            return redirect()->back()
                ->with('error', 'OTP has expired. Please request a new one.');
        }

        // Verify OTP
        if ($request->otp != session('otp')) {
            return redirect()->back()
                ->with('error', 'Invalid OTP. Please try again.');
        }

        // Mark phone as verified
        $user->phone_verified_at = now();
        $user->is_verified = true;
        $user->save();

        // Clear OTP from session
        session()->forget(['otp', 'otp_user_id', 'otp_expires_at']);

        // Redirect to complete profile
        return redirect()->route('profile.complete', ['user_id' => $userId])
            ->with('success', 'Phone verified successfully! Please complete your profile.');
    }

    /**
     * Resend OTP
     */
    public function resendOTP($userId)
    {
        $user = User::findOrFail($userId);

        // Generate new OTP
        $otp = rand(100000, 999999);
        
        // Store in session
        session([
            'otp' => $otp,
            'otp_user_id' => $user->user_id,
            'otp_expires_at' => now()->addMinutes(10)
        ]);

        // Send OTP email
        Mail::to($user->email)->send(new OTPVerificationEmail($user, $otp));

        return redirect()->back()
            ->with('success', 'New OTP has been sent to your email.');
    }

    /**
     * Resend verification email
     */
    public function resendVerificationEmail($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->email_verified_at) {
            return redirect()->back()
                ->with('info', 'Email already verified.');
        }

        // Generate new verification token
        $verificationToken = Str::random(64);
        $user->verification_token = $verificationToken;
        $user->save();

        // Send verification email
        Mail::to($user->email)->send(new VerificationEmail($user, $verificationToken));

        return redirect()->back()
            ->with('success', 'Verification email has been resent. Please check your inbox.');
    }

    /**
     * Show profile completion form
     */
    public function showProfileForm($userId)
    {
        $user = User::findOrFail($userId);

        // Check if user is verified
        if (!$user->is_verified) {
            return redirect()->route('verification.notice', ['user_id' => $userId])
                ->with('error', 'Please verify your account first.');
        }

        // Check if profile already completed
        if ($user->user_type == 'Volunteer' && $user->volunteerProfile) {
            return redirect()->route('login')
                ->with('success', 'Profile already completed. Please login.');
        }
        if ($user->user_type == 'Organization' && $user->organization) {
            return redirect()->route('login')
                ->with('success', 'Profile already completed. Please login.');
        }

        return view('auth.complete-profile', compact('user'));
    }

    /**
     * Complete profile
     */
    public function completeProfile(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        if ($user->user_type == 'Volunteer') {
            return $this->completeVolunteerProfile($request, $user);
        } else {
            return $this->completeOrganizationProfile($request, $user);
        }
    }

    /**
     * Complete volunteer profile
     */
    private function completeVolunteerProfile(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'occupation' => 'nullable|string|max:100',
            'education_level' => 'nullable|in:High School,Diploma,Bachelor,Master,PhD',
            'university' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:1000',
            'skills' => 'nullable|string',
            'interests' => 'nullable|string',
            'availability' => 'required|in:Weekdays,Weekends,Flexible,Full-time',
            'volunteer_experience' => 'nullable|string',
            'preferred_location' => 'nullable|string|max:100',
            'transportation' => 'nullable|in:Motorbike,Car,Public Transport,Walking',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle avatar upload
        $avatarUrl = null;
        if ($request->hasFile('avatar')) {
            $avatarUrl = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_url = 'storage/' . $avatarUrl;
            $user->save();
        }

        // Create volunteer profile
        VolunteerProfile::create([
            'user_id' => $user->user_id,
            'occupation' => $request->occupation,
            'education_level' => $request->education_level,
            'university' => $request->university,
            'bio' => $request->bio,
            'skills' => $request->skills,
            'interests' => $request->interests,
            'availability' => $request->availability,
            'volunteer_experience' => $request->volunteer_experience,
            'preferred_location' => $request->preferred_location,
            'transportation' => $request->transportation,
        ]);

        // Auto-login user
        auth()->login($user);

        return redirect()->route('volunteer.dashboard')
            ->with('success', 'Welcome to Volunteer Connect! Your profile is complete.');
    }

    /**
     * Complete organization profile
     */
    private function completeOrganizationProfile(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'organization_name' => 'required|string|max:150',
            'organization_type' => 'required|in:NGO,NPO,Charity,School,Hospital,Community Group',
            'description' => 'required|string',
            'mission_statement' => 'nullable|string',
            'website' => 'nullable|url|max:100',
            'contact_person' => 'nullable|string|max:100',
            'registration_number' => 'required|string|max:50',
            'founded_year' => 'nullable|numeric|min:1900|max:' . date('Y'),
            'registration_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoUrl = $request->file('logo')->store('logos', 'public');
            $user->avatar_url = 'storage/' . $logoUrl;
            $user->save();
        }

        // Handle registration document upload
        $documentUrl = null;
        if ($request->hasFile('registration_document')) {
            $documentUrl = $request->file('registration_document')->store('documents', 'public');
        }

        // Create organization profile
        Organization::create([
            'user_id' => $user->user_id,
            'organization_name' => $request->organization_name,
            'organization_type' => $request->organization_type,
            'description' => $request->description,
            'mission_statement' => $request->mission_statement,
            'website' => $request->website,
            'contact_person' => $request->contact_person,
            'registration_number' => $request->registration_number,
            'founded_year' => $request->founded_year,
            'registration_document' => $documentUrl ? 'storage/' . $documentUrl : null,
            'verification_status' => 'Pending',
        ]);

        // Auto-login user
        auth()->login($user);

        return redirect()->route('organization.dashboard')
            ->with('success', 'Welcome! Your organization profile is under review.');
    }
}
