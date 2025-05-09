<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PollController;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/poll/{id}', [PollController::class, 'show'])->name('poll_created');

