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
    public $creator_url;

    public function __construct(Poll $poll, $creator_url)
    {
        $this->poll = $poll;
        $this->creator_url = $creator_url;
    }

    public function build()
    {
        return $this->subject('Je poll "' . $this->poll->title . '" is succesvol aangemaakt!')
                    ->view('mails.poll_created');
    }
}


