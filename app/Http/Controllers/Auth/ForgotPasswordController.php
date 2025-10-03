<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Mail\ResetPasswordEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class ForgotPasswordController extends Controller{
    public function showLinkRequestForm(){
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);
        if($validator->fails()){
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::where('email', $request->email)->first();
        $token = Str::random(62);
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );
        Mail::to($user->email)->send(new ResetPasswordEmail($user, $token));
        return redirect()->back()->with('status', 'We have emailed your password reset link!');
    }

    public function showResetForm($token){
        return view('auth.reset-password', ['token' => $token]);
    }

    public function reset(Request $request){
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);
        if($validator->fails()){
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

        $passwordReset = DB::table('password_resets')->where('email', $request->email)->first();
        if(!$passwordReset || !Hash::check($request->token, $passwordReset->token)){
            return redirect()->back()->with('error', 'Invalid or expired password reset token.')
                ->withInput($request->only('email'));
        }
        if (now()->diffInHours($passwordReset->created_at) > 24) {
            return redirect()->back()
                ->with('error', 'Password reset token has expired. Please request a new one.')
                ->withInput($request->only('email'));
        }
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();
        DB::table('password_resets')->where('email', $request->email)->delete();
        return redirect()->route('login')->with('success', 'Password has been reset successfully. Please login again.');
    }
}