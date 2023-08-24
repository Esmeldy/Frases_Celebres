<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AutoresController;
use App\Http\Controllers\CategoriasController;
use App\Http\Controllers\FrasesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/logout', [AuthController::class, 'logout']);

    Route::apiResource('autores', AutoresController::class)
        ->only(['store', 'update', 'destroy'])
        ->missing(function (Request $request) {
            return Redirect::route('frases.index');
        });

    Route::apiResource('categorias', CategoriasController::class)
        ->only(['store', 'update', 'destroy']);

    Route::apiResource('frases', FrasesController::class)
        ->only(['store', 'update', 'destroy']);
});

Route::resource('categorias', CategoriasController::class)
    ->only(['index', 'show']);

Route::resource('autores', AutoresController::class)
    ->only(['index', 'show']);

Route::get('/frases/random', [FrasesController::class, 'getRandomFrases'])->name('random');
Route::apiResource('frases', FrasesController::class)
    ->only(['index', 'show'])
    ->missing(function (Request $request) {
        return Redirect::route('frases.index');
    });
