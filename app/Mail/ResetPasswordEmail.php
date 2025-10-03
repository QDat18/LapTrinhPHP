<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class ResetPasswordEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $resetUrl;

    public function __construct(User $user, $token)
    {
        $this->user = $user;
        $this->resetUrl = route('password.reset', ['token' => $token]);
    }

    public function build()
    {
        return $this->subject('Reset Your Password - Volunteer Connect')
                    ->view('emails.reset-password');
    }
}