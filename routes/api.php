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
