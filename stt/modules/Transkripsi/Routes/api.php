<?php

use Illuminate\Support\Facades\Route;
use Modules\Transkripsi\Controllers\TranscriptionController;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::apiResource('transcriptions', TranscriptionController::class)->only(['index', 'store', 'show']);
});
