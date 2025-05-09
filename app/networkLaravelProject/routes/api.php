<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PollController;
use App\Http\Controllers\VoteController;

Route::post('/poll/create', [PollController::class, 'createPoll']);
Route::post('/vote', [VoteController::class, 'store']);
