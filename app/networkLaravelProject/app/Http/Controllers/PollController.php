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
                'dates' => 'required|array|min:1',
                'dates.*' => 'required|date',
                'emails' => 'required|array|min:1',
                'emails.*' => 'required|email|max:255'
            ]);

            $poll = Null;
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

    public function addDates(Request $request, Poll $poll)
    {
        try {
            $validated = $request->validate([
                'dates' => 'required|array|min:1',
                'dates.*' => 'required|date',
            ]);

            $dates = $this->pollService->addDates($poll, $validated['dates']);

            // Log de aanmaak van de poll dates
            Log::info("PollController@addDates", ['poll' => $poll, 'dates' => $dates]);

            return response()->json(['message' => 'Data succesvol toegevoegd', 'dates' => $dates], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validatie mislukt', 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            Log::error("PollController@addDates - Fout: {$e->getMessage()}", ['poll_id' => $poll->id]);
            return response()->json(['message' => 'Interne fout bij toevoegen data'], 500);
        }
    }

    public function addParticipants(Request $request, Poll $poll) {
        try {
            $validated = $request->validate([
                'emails' => 'required|array|min:1',
                'emails.*' => 'required|email|max:255'
            ]);

            $participants = $this->pollService->addParticipants($poll, $validated['emails']);

            // Log de aanmaak van de participants
            Log::info("PollController@addParticipants", ['poll' => $poll, 'participants' => $participants]);

            return response()->json(['message' => 'Deelnemer(s) succesvol toegevoegd', 'participants' => $participants], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validatie mislukt', 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            Log::error("PollController@addParticipants - Fout: {$e->getMessage()}", ['poll_id' => $poll->id]);
            return response()->json(['message' => 'Interne fout bij toevoegen deelnemers'], 500);
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

    public function showVotingPage(Poll $poll, Participant $participant, $token)
    {
        // Verifieer of token klopt
        if ($participant->vote_token !== $token) {
            abort(403, 'Ongeldige of verlopen stemlink.');
        }

        if ($participant->poll_id !== $poll->id) {
            abort(403, 'Deze deelnemer hoort niet bij deze poll.');
        }

        $participantVotes = $this->pollService->getParticipantVotes($poll, $participant);
        $votes = $this->pollService->getPollVotes($poll);
        $isCreator = $participant->email === $poll->email_creator;
        $viewOnly = $poll->isEnded();

        Log::info("PollController@showVotingPage", [
            'poll_id' => $poll->id,
            'participant_id' => $participant->id,
            'votes' => $votes,
            'participantVotes' => $participantVotes,
            'isCreator' => $isCreator,
            'viewOnly' => $viewOnly,
        ]);

        return view('poll', [
            'poll' => $poll,
            'votes' => $votes,
            'participantVotes' => $participantVotes,
            'participant' => $participant,
            'isCreator' => $isCreator,
            'viewOnly' => $viewOnly,
        ]);
    }
}
