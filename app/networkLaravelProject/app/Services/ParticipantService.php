<?php

namespace App\Services;

use Throwable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

use App\Models\Participant;

class ParticipantService
{
    public function getParticipantById(int $id): ?Participant
    {
        $participant = Participant::find($id);
        return $participant ?: null;
    }
    public function getParticipantByEmail(string $email): ?Participant
    {
        $participant = Participant::where('email', $email)->first();
        return $participant ?: null;
    }

    public function getParticipantURL(Participant $participant): ?string
    {
        if ($participant) {
            return url("/poll/{$participant->poll_id}/vote/{$participant->id}/{$participant->vote_token}");
        }
        return null;
    }

    public function setParticipantVoted(int $id): void
    {
        try {
            $participant = $this->getParticipantById($id);
            if ($participant) {
                $participant->has_voted = true;
                $participant->save();
            }
        } catch (Throwable $e) {
            Log::error("ParticipantService@setParticipantVoted - Error: {$e->getMessage()}", [
                'participant_id' => $id,
            ]);
            throw $e;
        }
    }
    public function createParticipant(int $poll_id, string $email): Participant
    {
        try {
            $validator = Validator::make(['email' => $email, 'poll_id' => $poll_id], [
                'email' => 'required|email|max:255',
                'poll_id' => 'required|exists:polls,id',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // Generate a unique token for the participant
            $vote_token = Str::random(8);

            $participant =  Participant::create([
                'poll_id' => $poll_id,
                'email' => $email,
                'vote_token' => $vote_token,
            ]);

            return $participant;

        } catch (ValidationException $e) {
            Log::warning("ParticipantService@createPollDate - Validatie error", [
                'errors' => $e->errors(),
                'poll_id' => $poll_id,
                'email' => $email,
            ]);
            throw $e;
        } catch (Throwable $e) {
            Log::error("ParticipantService@createPollDate - Error: {$e->getMessage()}", [
                'poll_id' => $poll_id,
                'email' => $email,
            ]);
            throw $e;
        }
    }
}
