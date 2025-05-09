<?php

namespace App\Services;


use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

use App\Models\Poll;
use App\Models\Participant;
use App\Mail\PollCreated;
use App\Mail\PollEnded;
use App\Services\PollDateService;
use App\Services\ParticipantService;

class PollService
{
    public $pollDateService;
    public $participantService;

    public function __construct(PollDateService $pollDateService, ParticipantService $participantService)
    {
        $this->pollDateService = $pollDateService;
        $this->participantService = $participantService;
    }

    public function getPollById(int $id): ?Poll
    {
        $poll = Poll::find($id);
        return $poll ?: null;
    }

    public function getAllParticipants(Poll $poll): Collection
    {
        return $poll->participants()->get();
    }

    private function getCreator(Poll $poll): ?Participant
    {
        $creator = $poll->participants()->where('email', $poll->email_creator)->first();
        return $creator ?: null;
    }


    private function createPoll(array $data): Poll
    {
        try {
            $validator = Validator::make($data, [
                'email_creator' => 'required|email|max:255',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'location' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            return Poll::create([
                'email_creator' => $data['email_creator'],
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'location' => $data['location'],
            ]);
        } catch (ValidationException $e) {
            Log::warning("PollService@createPoll - Validation error", [
                'errors' => $e->errors(),
                'data' => $data
            ]);
            throw $e;
        } catch (Throwable $e) {
            Log::error("PollService@createPoll - Error: {$e->getMessage()}", ['data' => $data]);
            throw $e;
        }
    }

    public function addDates(Poll $poll, string $dates): array
    {
        $allDates = array_unique(array_map('trim', explode(',', $dates)));
        $pollDates = [];

        foreach ($allDates as $date) {
            try {
                $pollDate = $this->pollDateService->createPollDate($poll->id, $date);
                $pollDates[] = $pollDate;
            } catch (ValidationException $e) {
                Log::warning("PollService@addDates - Ongeldige datum: $date", [
                    'poll_id' => $poll->id,
                    'errors' => $e->errors(),
                ]);
                throw $e;
            } catch (Throwable $e) {
                Log::error("PollService@addDates - Fout bij datum: $date", [
                    'poll_id' => $poll->id,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }
        return $pollDates;
    }


    public function addParticipants(Poll $poll, string $emails): array
    {
        $allEmails = array_unique(array_map('trim', explode(',', $emails)));

        $participants = [];

        foreach ($allEmails as $email) {
            try {
                $participant = $this->participantService->createParticipant($poll->id, $email);
                $participants[] = $participant;
            } catch (ValidationException $e) {
                Log::warning("PollService@addParticipants - Ongeldig e-mailadres: $email", [
                    'poll_id' => $poll->id,
                    'errors' => $e->errors(),
                ]);
                throw $e;
            } catch (Throwable $e) {
                Log::error("PollService@addParticipants - Fout bij e-mailadres: $email", [
                    'poll_id' => $poll->id,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }
        return $participants;
    }

    public function createPollWithDatesAndParticipants(array $data): Poll
    {
        try {
            $poll = $this->createPoll($data);
            $this->addDates($poll, $data['dates']);
            $this->addParticipants($poll, $data['email_creator']);
            $this->addParticipants($poll, $data['emails']);
            $creator = $this->getCreator($poll);
            $creator_url = url("/poll/{$poll->id}/vote/{$creator->id}/{$creator->vote_token}");
            Mail::to($poll->email_creator)->send(new PollCreated($poll, $creator_url));
            return $poll;
        } catch (ValidationException $e) {
            Log::warning("PollService@createPollWithDatesAndParticipants - Validation error", [
                'errors' => $e->errors(),
                'data' => $data
            ]);
            throw $e;
        } catch (Throwable $e) {
            Log::error("PollService@createPollWithDatesAndParticipants - Error: {$e->getMessage()}", ['data' => $data]);
            throw $e;
        }
    }

    public function getPollVotes(Poll $poll): array
    {
        $votes = [];
        foreach ($poll->pollDates as $date) {
            $votes[$date->date] = $date->votes()->count();
        }
        return $votes;
    }

    private function checkIfEveryoneVoted(Poll $poll): bool
    {
        $participants = $poll->participants()->get();
        $votedParticipants = $participants->filter(function ($participant) {
            return $participant->has_voted;
        });

        return $votedParticipants->count() === $participants->count();
    }

    private function determineBestDate(Poll $poll): ?string
    {
        $dates = $poll->pollDates()->get();
        $bestDate = null;
        $maxVotes = 0;

        foreach ($dates as $date) {
            $votes = $date->votes()->count();
            if ($votes > $maxVotes) {
                $maxVotes = $votes;
                $bestDate = $date->date;
            }
        }
        return $bestDate;
    }

    public function endPoll(Poll $poll): ?string
    {
        $bestDate = $this->determineBestDate($poll);
        if ($bestDate) {
            Log::info("PollService@endPoll - Poll {$poll->id} is beëindigd. Beste datum: $bestDate");
            $participants = $this->getAllParticipants($poll);
            foreach ($participants as $participant) {
                Mail::to($participant->email)->send(new PollEnded($poll, $bestDate));
            }
            return $bestDate;
        }
        Log::info("PollService@endPoll - Poll {$poll->id} is beëindigd. Geen stemmen.");
        return null;
    }

    public function checkIfEveryoneVotedAndEndPoll(Poll $poll): ?string
    {
        if ($this->checkIfEveryoneVoted($poll)) {
            return $this->endPoll($poll);
        }
        Log::info("PollService@checkIfEveryoneVotedAndEndPoll - Niet iedereen heeft gestemd voor poll {$poll->id}");
        return null;
    }
}
