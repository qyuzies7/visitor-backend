<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifikasiVisitorCard extends Mailable
{
    use Queueable, SerializesModels;

    public $messageText;

    public function __construct($messageText)
    {
        $this->messageText = $messageText;
    }

    public function build()
    {
        return $this->subject('Notifikasi Visitor Card')
            ->view('emails.notifikasi_visitor_card')
            ->with(['messageText' => $this->messageText]);
    }
}
