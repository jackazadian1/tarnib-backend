<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TarnibController;
use App\Http\Controllers\PokerController;
use App\Http\Controllers\AuthController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('create',  [TarnibController::class, 'createRoom']);
Route::get('join',  [TarnibController::class, 'joinRoom']);
Route::post('chooseSeat',  [TarnibController::class, 'chooseSeat']);
Route::get('roundInfo',  [TarnibController::class, 'getRoundInfo']);
Route::post('bid',  [TarnibController::class, 'bid']);
Route::post('setTarnib',  [TarnibController::class, 'setTarnib']);
Route::post('playCard',  [TarnibController::class, 'playCard']);
Route::post('setNewTurn',  [TarnibController::class, 'setNewTurn']);
Route::post('setNewRound',  [TarnibController::class, 'setNewRound']);
Route::post('moveToNewRoom',  [TarnibController::class, 'moveToNewRoom']);
Route::get('getAnalytics',  [TarnibController::class, 'getAnalytics']);
Route::get('test',  [TarnibController::class, 'test']);


//poker
Route::post('create_poker',  [PokerController::class, 'createRoom']);
Route::get('pokerRoomData',  [PokerController::class, 'getData']);
Route::get('getPokerRooms',  [PokerController::class, 'getRooms']);
Route::get('pokerRoomPasswordCheck',  [PokerController::class, 'passwordCheck']);
Route::post('authenticate',  [PokerController::class, 'authenticate']);
Route::post('addPokerPlayer',  [PokerController::class, 'addPokerPlayer']);
Route::post('addChips',  [PokerController::class, 'addChips']);
Route::post('cashout',  [PokerController::class, 'cashout']);
