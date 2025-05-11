<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

use App\Models\Poll;
use App\Models\Participant;
use App\Services\VoteService;
use App\Services\PollService;

class VoteController extends Controller
{
    private $voteService;
    private $pollService;

    public function __construct(VoteService $voteService, PollService $PollService)
    {
        $this->voteService = $voteService;
        $this->pollService = $PollService;
    }

    public function vote(Request $request, Poll $poll, Participant $participant)
    {
        try {
            $validated = $request->validate([
                'dates' => 'required|array|min:1',
                'dates.*' => 'required|integer|exists:poll_dates,id',
                'vote_token' => 'required|string',
            ]);

            if (Participant::where('id', $participant->id)
                ->where('vote_token', $validated['vote_token'])
                ->doesntExist()) {
                return response()->json(['message' => 'Ongeldige stemtoken'], 403);
            }

            $this->voteService->storeVote($validated, $participant, $poll);
            $this->pollService->checkIfEveryoneVotedAndEndPoll($poll);

            return response()->json(['message' => 'Stemmen succesvol opgeslagen'], 201);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validatie mislukt', 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            Log::error("VoteController@vote - Error: {$e->getMessage()}");
            return response()->json(['message' => 'Interne serverfout'], 500);
        }
    }
}
