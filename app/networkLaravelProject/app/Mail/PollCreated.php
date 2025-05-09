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
        Log::info("PollCreated@build", [
            'poll_id' => $this->poll->id,
            'email_creator' => $this->poll->email_creator,
            'title' => $this->poll->title,
            'description' => $this->poll->description,
            'location' => $this->poll->location,
        ]);
        return $this->subject('Je poll is succesvol aangemaakt!')
                    ->view('emails.poll_created');
    }
}


