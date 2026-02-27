<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ScoreNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $result;

    public function __construct($result)
    {
        $this->result = $result;
    }

    public function build()
    {
        return $this->subject('Hasil Tes IELTS Reading Kamu')
                    ->html("
                        <h1>Halo!</h1>
                        <p>Kamu baru saja menyelesaikan tes IELTS Reading.</p>
                        <p><strong>Skor Kamu: " . $this->result->score . "</strong></p>
                        <p>Jawaban Benar: " . $this->result->correct_answers . " dari " . $this->result->total_questions . " soal.</p>
                        <br>
                        <p>Terima kasih sudah belajar bersama kami!</p>
                    ");
    }
}