<?php

use App\Http\Controllers\TranscribeAudioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::post('/transcribe', TranscribeAudioController::class)->name('transcribe');
