<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\Poll;
use App\Models\Participant;

class PollEnded extends Mailable
{
    public $poll;
    public $finalDate;

    public function __construct(Poll $poll, $finalDate)
    {
        $this->poll = $poll;
        $this->finalDate = $finalDate;
    }

    public function build()
    {
        return $this->subject('De poll "' . $this->poll->title . '" is beëindigd')
                    ->view('mails.poll_ended');
    }
}
