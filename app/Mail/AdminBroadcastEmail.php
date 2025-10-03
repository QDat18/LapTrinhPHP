<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminBroadcastEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $messageContent;
    public $recipient;

    /**
     * Create a new message instance.
     */
    public function __construct($subject, $messageContent, $recipient)
    {
        $this->subject = $subject;
        $this->messageContent = $messageContent;
        $this->recipient = $recipient;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        // Replace placeholders
        $message = str_replace(
            ['{name}', '{email}', '{first_name}', '{last_name}'],
            [
                $this->recipient->first_name . ' ' . $this->recipient->last_name,
                $this->recipient->email,
                $this->recipient->first_name,
                $this->recipient->last_name
            ],
            $this->messageContent
        );

        return $this->subject($this->subject)
                    ->view('emails.admin-broadcast')
                    ->with([
                        'messageContent' => $message,
                        'recipient' => $this->recipient
                    ]);
    }
}