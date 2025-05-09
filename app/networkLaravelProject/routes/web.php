<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PollController;

Route::get('/', function () {
    return view('home');
})->name('home');


Route::get('/poll/{poll}/vote/{participant}/{token}', [PollController::class, 'showVotingPage']);
Route::get('/poll/{poll}', [PollController::class, 'showVotingPageViewOnly']);

