<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Selamat Datang di Scholars IELTS!')
                    ->html("
                        <h1>Halo, " . $this->user->name . "!</h1>
                        <p>Terima kasih telah mendaftar di <strong>Scholars IELTS App</strong>.</p>
                        <p>Sekarang kamu bisa mulai mengerjakan latihan soal IELTS Reading dan memantau progres belajarmu langsung dari dashboard.</p>
                        <br>
                        <p>Selamat belajar dan semoga sukses mencapai band score impianmu!</p>
                        <br>
                        <p>Salam,<br>Tim Scholars IELTS</p>
                    ");
    }
}