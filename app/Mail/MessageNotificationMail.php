<?php

namespace App\Mail;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MessageNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Message $message, public string $body)
    {
    }

    public function build(): self
    {
        $subject = $this->message->subject ?? 'New message from Taisykla';

        return $this->subject($subject)
            ->view('emails.message-notification')
            ->with([
                'message' => $this->message,
                'body' => $this->body,
            ]);
    }
}
