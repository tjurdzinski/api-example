<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['json.response']], function () {
    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);

    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout']);
        Route::resource('vacancies', \App\Http\Controllers\VacancyController::class)->only([
            'index', 'show', 'store', 'update', 'destroy',
        ]);
        Route::resource('reservations', \App\Http\Controllers\ReservationController::class)->only([
            'index', 'show', 'store', 'update', 'destroy',
        ]);
    });

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });
});
