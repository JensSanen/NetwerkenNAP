<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

use App\Mail\PollInvitation;
use App\Models\Participant;

class ParticipantController extends Controller
{

    public function store(Request $request) {
        try {
            $request->validate([
                'poll_id' => 'required|integer|exists:polls,id',
                'email' => 'required|email',
            ]);

            $token = Str::random(32);

            $participant = Participant::create(array_merge(
                $request->only(['email', 'poll_id']),
                [
                    'has_voted' => false,
                    'vote_token' => $token,
                ]
            ));

            $voteUrl = url("/poll/{$participant->poll_id}/vote/{$participant->id}/{$participant->vote_token}");

            Log::info("ParticipantController@store - Vote URL: " . $voteUrl, [
                'poll_id' => $request->input('poll_id'),
                'email' => $request->input('email'),
                'vote_token' => $token,
                'url' => $voteUrl,
            ]);

            Mail::to($participant->email)->send(new PollInvitation($participant, $voteUrl));


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
