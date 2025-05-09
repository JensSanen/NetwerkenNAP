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

        $votes = $this->pollService->getPollVotes($poll);

        return view('poll', [
            'poll' => $poll,
            'votes' => $votes,
            'participant' => $participant,
        ]);
    }

    public function checkEndPoll(Poll $poll) {
        $date = $this->pollService->checkIfEveryoneVotedAndEndPoll($poll);
        if ($date) {
            return response()->json(['message' => 'Poll succesvol beëindigd', 'date' => $date], 200);
        } else {
            return response()->json(['message' => 'Poll is nog niet beëindigd'], 200);
        }
    }

    public function endPoll(Poll $poll) {
        try {
            $date = $this->pollService->endPoll($poll);
            return response()->json(['message' => 'Poll succesvol beëindigd', 'date' => $date], 200);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validatie mislukt', 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            Log::error("PollController@endPoll - Fout: {$e->getMessage()}", ['poll_id' => $poll->id]);
            return response()->json(['message' => 'Interne fout bij beëindigen poll'], 500);
        }
    }
}
