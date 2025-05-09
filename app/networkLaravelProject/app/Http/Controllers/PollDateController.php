<?php

namespace App\Http\Controllers;

use App\Models\PollDate;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PollDateController extends Controller
{

    public function store(Request $request) {
        try {
            $request->validate([
                'poll_id' => 'required|integer|exists:polls,id',
                'date' => 'required|date',
            ]);
            
            Log::info("PollDateController@store", [
                'poll_id' => $request->input('poll_id'),
                'date' => $request->input('date'),
            ]);

            $pollDate = PollDate::create($request->only(['poll_id', 'date']));


            return response()->json([
                'message' => 'PollDate created successfully',
                'pollDate' => $pollDate,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validatie mislukt',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("PollDateController@store - Error: " . $e->getMessage(), [
                'poll_id' => $request->input('poll_id'),
                'date' => $request->input('date'),
            ]);

            return response()->json([
                'message' => 'Er is een interne fout opgetreden',
            ], 500);
        }
    }
}
