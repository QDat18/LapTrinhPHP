<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class VerificationEmail extends Mailable{
    use Queueable, SerializesModels;
    public $user;
    public $verificationUrl;

    public function __construct(User $user, $token){
        $this->user = $user;
        $this->verificationUrl = route('verify-email', ['token' => $token]);
    }

    public function build(){
        return $this->subject('Verify Your Email Address - Volunteer Connect Pro')
                    ->view('emails.verify-email');
    }
}