<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

use App\Models\Vote;
use App\Models\Participant;

class VoteController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'poll_id' => 'required|integer|exists:polls,id',
                'participant_id' => 'required|integer|exists:participants,id',
                'dates' => 'required|array',
                'dates.*' => 'integer|exists:poll_dates,id',
            ]);

            $pollId = $request->input('poll_id');
            $participantId = $request->input('participant_id');
            $dates = $request->input('dates');

            Log::info("VoteController@store", [
                'poll_id' => $pollId,
                'participant_id' => $participantId,
                'dates' => $dates,
            ]);

            // Verwijder eerst eerdere stemmen van deze deelnemer (optioneel)
            Vote::where('participant_id', $participantId)->delete();

            // Voeg stemmen toe voor elke geselecteerde datum
            foreach ($dates as $dateId) {
                Vote::create([
                    'poll_date_id' => $dateId,
                    'participant_id' => $participantId,
                ]);
            }

            Participant::where('id', $participantId)->update(['has_voted' => true]);

            return response()->json([
                'message' => 'Stemmen succesvol opgeslagen',
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validatie mislukt',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("VoteController@store - Error: " . $e->getMessage());

            return response()->json([
                'message' => 'Er is een interne fout opgetreden',
            ], 500);
        }
    }
}
