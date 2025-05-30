<?php

namespace App\Services;

use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

use App\Mail\PollUpdated;
use App\Models\Poll;
use App\Models\Participant;
use App\Models\Vote;

class VoteService
{
    private $pollService;
    private $participantService;

    public function __construct(PollService $pollService, ParticipantService $participantService)
    {
        $this->participantService = $participantService;
        $this->pollService = $pollService;
    }
    public function storeVote(array $data, Participant $participant, Poll $poll): void
    {
        try {
            // Validatie
            $validator = Validator::make($data, [
                'dates' => 'required|array',
                'dates.*' => 'integer|exists:poll_dates,id',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // Verwijder oude stemmen
            Vote::where('participant_id', $participant->id)->delete();

            // Nieuwe stemmen
            foreach ($data['dates'] as $dateId) {
                Vote::create([
                    'poll_date_id' => $dateId,
                    'participant_id' => $participant->id,
                ]);
            }

            // Markeer als gestemd
            $this->participantService->setParticipantVoted($participant->id);
            $creator_url = $this->pollService->getCreatorURL($poll);

            Mail::to($poll->email_creator)->send(new PollUpdated($poll, $participant, $creator_url));

        } catch (ValidationException $e) {
            Log::warning("VoteService@storeVote - Validation error", [
                'errors' => $e->errors(),
                'data' => $data
            ]);
            throw $e;
        } catch (Throwable $e) {
            Log::error("VoteService@storeVote - Error: {$e->getMessage()}", $data);
            throw $e;
        }
    }
}
