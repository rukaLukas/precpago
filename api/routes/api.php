<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StatisticController;
use App\Http\Controllers\TransactionController;

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

Route::get('/health', function(){    
    return response()->json(['status' => 'ok']);
});

Route::post('/transactions', [TransactionController::class, 'store']);
Route::delete('/transactions', [TransactionController::class, 'destroy']);
Route::get('/statistics', [StatisticController::class, 'index']);