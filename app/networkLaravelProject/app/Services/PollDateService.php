<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;

use App\Models\PollDate;

class PollDateService
{
    public function createPollDate(int $poll_id, string $date): PollDate
    {
        try {
            $validator = Validator::make(['date' => $date, 'poll_id' => $poll_id], [
                'date' => 'required|date_format:Y-m-d',
                'poll_id' => 'required|exists:polls,id',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $parsedDate = Carbon::parse($date);

            return PollDate::create([
                'poll_id' => $poll_id,
                'date' => $parsedDate->toDateString(),
            ]);
        } catch (ValidationException $e) {
            Log::warning("PollDateService@createPollDate - Validatie error", [
                'errors' => $e->errors(),
                'poll_id' => $poll_id,
                'date' => $date,
            ]);
            throw $e;
        } catch (Throwable $e) {
            Log::error("PollDateService@createPollDate - Error: {$e->getMessage()}", [
                'poll_id' => $poll_id,
                'date' => $date,
            ]);
            throw $e;
        }
    }
}
