<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PollController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\WeatherController;

Route::post('/poll', [PollController::class, 'createPoll']);
Route::post('/poll/{poll}/addParticipants', [PollController::class, 'addParticipants']);
Route::post('/poll/{poll}/addDates', [PollController::class, 'addDates']);
Route::post('/poll/{poll}/vote/{participant}', [VoteController::class, 'vote']);
Route::post('/poll/{poll}/end', [PollController::class, 'endPoll']);

Route::get('/weather/{location}', [WeatherController::class, 'getWeatherForecast']);
