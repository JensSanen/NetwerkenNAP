<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Participant;

class PollInvitation extends Mailable
{
    public $participant;
    public $voteUrl;

    public function __construct(Participant $participant, $voteUrl)
    {
        $this->participant = $participant;
        $this->voteUrl = $voteUrl;
    }

    public function build()
    {
        return $this->subject('Je bent uitgenodigd om te stemmen')
                    ->view('mails.poll_invitation');
    }
}




