<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ParticipantController extends Controller
{

    public function store(Request $request) {
        try {
            $request->validate([
                'poll_id' => 'required|integer|exists:polls,id',
                'email' => 'required|email',
            ]);

            Log::info("ParticipantController@store", [
                'poll_id' => $request->input('poll_id'),
                'email' => $request->input('email'),
            ]);

            $participant = Participant::create($request->only(['email', 'poll_id']));


            return response()->json([
                'message' => 'Participant created successfully',
                'participant' => $participant,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validatie mislukt',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("ParticipantController@store - Error: " . $e->getMessage(), [
                'email' => $request->input('email'),
                'poll_id' => $request->input('poll_id'),
            ]);

            return response()->json([
                'message' => 'Er is een interne fout opgetreden',
            ], 500);
        }
    }
}
