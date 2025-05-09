<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\Poll;
use App\Models\Participant;

class PollUpdated extends Mailable
{
    public $participant;
    public $poll;

    public function __construct(Poll $poll, Participant $participant)
    {
        $this->participant = $participant;
        $this->poll = $poll;
    }

    public function build()
    {
        return $this->subject('Er is gestemd in de poll "' . $this->poll->title . '"')
                    ->view('mails.poll_updated');
    }
}
