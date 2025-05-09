<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PollController;

Route::post('/poll/create', [PollController::class, 'createPoll']);
