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
    public $creatorUrl;

    public function __construct(Poll $poll, Participant $participant, $creatorUrl)
    {
        $this->participant = $participant;
        $this->poll = $poll;
        $this->creatorUrl = $creatorUrl;
    }

    public function build()
    {
        return $this->subject('Er is gestemd in de poll "' . $this->poll->title . '"')
                    ->view('mails.poll_updated');
    }
}
