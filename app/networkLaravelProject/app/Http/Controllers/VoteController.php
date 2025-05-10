<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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

    public function vote(Request $request)
    {
        try {
            $validated = $request->validate([
                'poll_id' => 'required|integer|exists:polls,id',
                'participant_id' => 'required|integer|exists:participants,id',
                'dates' => 'required|array',
                'dates.*' => 'integer|exists:poll_dates,id',
                'vote_token' => 'required|string',
            ]);

            if (Participant::where('id', $validated['participant_id'])
                ->where('vote_token', $validated['vote_token'])
                ->doesntExist()) {
                return response()->json(['message' => 'Ongeldige stemtoken'], 403);
            }

            $poll = $this->pollService->getPollById($validated['poll_id']);

            $this->voteService->storeVote($validated);
            $this->pollService->checkIfEveryoneVotedAndEndPoll($poll);

            return response()->json(['message' => 'Stemmen succesvol opgeslagen'], 201);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validatie mislukt', 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            Log::error("VoteController@store - Error: {$e->getMessage()}");
            return response()->json(['message' => 'Interne serverfout'], 500);
        }
    }
}
