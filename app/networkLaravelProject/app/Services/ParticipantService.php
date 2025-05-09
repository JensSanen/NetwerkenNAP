<?php

namespace App\Services;

use Throwable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

use App\Models\Participant;
use App\Mail\PollInvitation;

class ParticipantService
{
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


            $voteUrl = url("/poll/{$poll_id}/vote/{$participant->id}/{$vote_token}");

            // Mail::to($email)->send(new PollInvitation($participant, $voteUrl));

            return $participant;

        } catch (ValidationException $e) {
            Log::warning("ParticipantService@createPollDate - Validation error", [
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
