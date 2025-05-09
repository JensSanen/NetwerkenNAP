<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;


use App\Models\Poll;
use App\Mail\PollCreated;
use App\Http\Controllers\PollDateController;
use App\Http\Controllers\ParticipantController;

class PollController extends Controller
{
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
            // Validatie
            $request->validate([
                'email_creator' => 'required|email|max:255',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'location' => 'required|string|max:255',
                'dates' => 'required|string',
            ]);

            Log::info("PollController@createPoll", [
                'email_creator' => $request->input('email_creator'),
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'location' => $request->input('location'),
                'dates' => $request->input('dates'),
                'emails' => $request->input('emails'),
            ]);

            // Poll aanmaken
            $poll = Poll::create($request->only(['email_creator', 'title', 'description', 'location']));

            // Split dates en voeg ze toe via de controller
            $dates = array_map('trim', explode(',', $request->input('dates')));
            $pollDateController = new PollDateController();

            foreach ($dates as $date) {
                $pollDateRequest = new Request([
                    'poll_id' => $poll->id,
                    'date' => $date,
                ]);

                $pollDateController->store($pollDateRequest);
            }

            // Split emails en voeg ze toe via de controller
            $emails = array_map('trim', explode(',', $request->input('emails')));
            $participantController = new ParticipantController();
            $emails = array_merge($emails, [$request->input('email_creator')]); // Voeg de creator toe aan de deelnemerslijst
            foreach ($emails as $email) {
                $participantRequest = new Request([
                    'poll_id' => $poll->id,
                    'email' => $email,
                ]);

                $participantController->store($participantRequest);
            }

            // Stuur een e-mail naar de creator
            Mail::to($poll->email_creator)->send(new PollCreated($poll));

            return response()->json([
                'message' => 'Poll succesvol aangemaakt',
                'poll' => $poll,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validatie mislukt',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("PollController@createPoll - Error: " . $e->getMessage(), [
                'email_creator' => $request->input('email_creator'),
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'location' => $request->input('location'),
                'dates' => $request->input('dates'),
            ]);
            return response()->json([
                'message' => 'Fout bij aanmaken poll',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPollInfo($id)
    {
        try {
            $poll = Poll::with(['pollDates', 'participants'])->findOrFail($id);
            Log::info("PollController@getPollInfo", [
                'poll_id' => $id,
                'poll' => $poll,
            ]);

            return response()->json([
                'poll' => $poll,
            ], 200);
        } catch (\Exception $e) {
            Log::error("PollController@getPollInfo - Error: " . $e->getMessage(), [
                'poll_id' => $id,
            ]);
            return response()->json([
                'message' => 'Poll niet gevonden',
            ], 404);
        }
    }
}
