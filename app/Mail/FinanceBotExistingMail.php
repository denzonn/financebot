<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FinanceBotExistingMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $waUrl;

    public function __construct($name, $email, $waUrl)
    {
        $this->name = $name;
        $this->email = $email;
        $this->waUrl = $waUrl;
    }

    public function build()
    {
        return $this
            ->subject('Email FinanceBot Sudah Terdaftar')
            ->view('emails.financebot-existing');
    }
}
