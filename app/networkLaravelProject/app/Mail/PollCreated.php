<?php

namespace App\Mail;

use App\Models\Poll;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PollCreated extends Mailable
{
    public $poll;

    public function __construct(Poll $poll)
    {
        $this->poll = $poll;
    }

    public function build()
    {
        return $this->subject('Je poll is succesvol aangemaakt!')
                    ->view('mails.poll_created');
    }
}


