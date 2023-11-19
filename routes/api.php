<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Authorization\LoginController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DuelController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout']);

Route::group(['middleware' => ['auth:sanctum']], function () {

    //START THE DUEL
    Route::post('duels', [DuelController::class, 'startDuel']);

    //CURRENT GAME DATA
    Route::get('duels/active', [DuelController::class, 'getCurrentDuelData']);

    //User has just selected a card
    Route::post('duels/action', [DuelController::class, 'selectCard']);

    //DUELS HISTORY
    Route::get('duels', [DuelController::class, 'getDuelHistory']);
    
    
    Route::post('cards', [UserController::class, 'drawCard']);

    //USER DATA
    Route::get('user-data', [UserController::class, 'userData']);
});
