<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RaffleApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Scope endpoints
Route::get('/raffles', [RaffleApiController::class, 'index']);
Route::get('/raffles/{id}', [RaffleApiController::class, 'show']);
Route::post('/raffles/{id}/reserve', [RaffleApiController::class, 'reserve']);
Route::post('/raffles/{id}/purchase', [RaffleApiController::class, 'purchase']);
Route::get('/raffles/{id}/winners', [RaffleApiController::class, 'winners']);
Route::get('/raffles/{raffle}/numbers/search', [RaffleApiController::class, 'searchTickets'])->middleware('throttle:search');
Route::post('/coupons/validate', [\App\Http\Controllers\Api\CouponController::class, 'validate']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/waitlist/{raffle}/join', [\App\Http\Controllers\WaitlistController::class, 'join']);
    Route::delete('/waitlist/{raffle}/leave', [\App\Http\Controllers\WaitlistController::class, 'leave']);
});
