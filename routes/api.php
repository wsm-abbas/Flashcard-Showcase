<?php

use App\Http\Controllers\Api\StudyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Study API routes with rate limiting
Route::prefix('study')->middleware(['throttle:60,1', 'api'])->group(function () {
    Route::post('/initialize', [StudyController::class, 'initializeStudy']);
    Route::get('/next-card', [StudyController::class, 'getNextCard']);
    Route::post('/submit-review', [StudyController::class, 'submitReview']);
    Route::get('/statistics', [StudyController::class, 'getStatistics']);
    Route::get('/status', [StudyController::class, 'getStudyStatus']);
});
