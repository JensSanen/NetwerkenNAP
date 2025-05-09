<?php

namespace App\Services;

use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

use App\Mail\PollUpdated;
use App\Models\Participant;
use App\Models\Vote;

class VoteService
{
    private $pollService;

    public function __construct(PollService $pollService)
    {
        $this->pollService = $pollService;
    }
    public function storeVote(array $data): void
    {
        try {
            // Validatie
            $validator = Validator::make($data, [
                'poll_id' => 'required|integer|exists:polls,id',
                'participant_id' => 'required|integer|exists:participants,id',
                'dates' => 'required|array',
                'dates.*' => 'integer|exists:poll_dates,id',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // Verwijder oude stemmen
            Vote::where('participant_id', $data['participant_id'])->delete();

            // Nieuwe stemmen
            foreach ($data['dates'] as $dateId) {
                Vote::create([
                    'poll_date_id' => $dateId,
                    'participant_id' => $data['participant_id'],
                ]);
            }

            // Markeer als gestemd
            Participant::where('id', $data['participant_id'])->update(['has_voted' => true]);


            $poll = $this->pollService->getPollById($data['poll_id']);
            $participant = Participant::find($data['participant_id']);
            // Mail::to($poll->email_creator)->send(new PollUpdated($poll, $participant));

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
