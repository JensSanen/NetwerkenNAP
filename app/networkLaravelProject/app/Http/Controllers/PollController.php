<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

use App\Models\Poll;
use App\Models\Participant;
use App\Services\PollService;

class PollController extends Controller
{
    private $pollService;

    public function __construct(PollService $pollService)
    {
        $this->pollService = $pollService;
    }

    public function show($id)
    {
        $poll = Poll::with(['pollDates', 'participants'])->findOrFail($id);

        return view('poll', [
            'poll' => $poll,
        ]);
    }

    public function createPoll(Request $request)
    {
        try {
            $validated = $request->validate([
                'email_creator' => 'required|email|max:255',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'location' => 'required|string|max:255',
                'dates' => 'required|string',
                'emails' => 'nullable|string'
            ]);

            $poll = $this->pollService->createPollWithDatesAndParticipants($validated);

            // Log de aanmaak van de poll
            Log::info("PollController@createPoll", ['poll' => $poll]);

            return response()->json(['message' => 'Poll succesvol aangemaakt', 'poll' => $poll], 201);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validatie mislukt', 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            Log::error("PollController@createPoll - Fout: {$e->getMessage()}", ['data' => $request->all()]);
            return response()->json(['message' => 'Interne fout bij aanmaken poll'], 500);
        }
    }

    public function showVotingPage(Poll $poll, Participant $participant, $token)
    {
        // Verifieer of token klopt
        if ($participant->vote_token !== $token) {
            abort(403, 'Ongeldige of verlopen stemlink.');
        }

        // (Optioneel) check of deelnemer bij deze poll hoort
        if ($participant->poll_id !== $poll->id) {
            abort(403, 'Deze deelnemer hoort niet bij deze poll.');
        }

        return view('poll', [
            'poll' => $poll,
            'participant' => $participant,
        ]);
    }

    public function checkEverybodyVoted(Poll $poll) {
        try {
            $participantsAmount = Participant::where('poll_id', $poll->id)->count();
            $votesAmount = Participant::where('poll_id', $poll->id)->where('has_voted', true)->count();

            if ($participantsAmount === $votesAmount) {
                return response()->json([
                    'message' => 'Alle deelnemers hebben gestemd.',
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Niet alle deelnemers hebben gestemd.',
                ], 200);
            }
        }
        catch (\Exception $e) {
            Log::error("PollController@checkEverybodyVoted - Error: " . $e->getMessage(), [
                'poll_id' => $poll->id,
            ]);
            return response()->json([
                'message' => 'Fout bij het controleren van stemmen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
