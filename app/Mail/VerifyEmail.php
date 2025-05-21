<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class VerifyEmail extends Mailable
{
    public $verificationUrl;

    public function __construct($verificationUrl)
    {
        $this->verificationUrl = $verificationUrl;
    }

    public function build()
    {
        return $this->subject('이메일 인증 요청')
                    ->view('emails.verify_email')
                    ->with(['url' => $this->verificationUrl]);
    }
}