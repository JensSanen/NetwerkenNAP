<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PollController;

Route::get('/poll/{poll_id}', [PollController::class, 'getPollInfo']);
Route::post('/poll/create', [PollController::class, 'createPoll']);
