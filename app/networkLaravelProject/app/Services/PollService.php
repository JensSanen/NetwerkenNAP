<?php

namespace App\Services;

use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

use App\Models\Poll;

use App\Mail\PollCreated;
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

    private function addDates(Poll $poll, string $dates): void
    {
        foreach (explode(',', $dates) as $date) {
            try {
                $this->pollDateService->createPollDate($poll->id, trim($date));
            } catch (ValidationException $e) {
                Log::warning("PollService@addDates - Ongeldige datum: $date", [
                    'poll_id' => $poll->id,
                    'errors' => $e->errors(),
                ]);
                throw $e; // of: continue als je wil doorgaan met volgende datum
            } catch (Throwable $e) {
                Log::error("PollService@addDates - Fout bij datum: $date", [
                    'poll_id' => $poll->id,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }
    }

    private function addParticipants(Poll $poll, string $emails, string $creatorEmail): void
    {
        $allEmails = array_unique(array_merge(
            array_map('trim', explode(',', $emails)),
            [$creatorEmail]
        ));

        foreach ($allEmails as $email) {
            try {
                $this->participantService->createParticipant($poll->id, $email);
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
    }

    public function createPollWithDatesAndParticipants(array $data): Poll
    {
        try {
            $poll = $this->createPoll($data);
            $this->addDates($poll, $data['dates']);
            $this->addParticipants($poll, $data['emails'], $data['email_creator']);
            Mail::to($poll->email_creator)->send(new PollCreated($poll));
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
}
