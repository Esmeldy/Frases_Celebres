<?php

use App\Http\Controllers\AutoresController;
use App\Http\Controllers\CategoriasController;
use App\Http\Controllers\FrasesController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/frases/random', [FrasesController::class, 'getRandomFrases']);
Route::apiResource('frases', FrasesController::class)
    ->missing(function (Request $request) {
        return Redirect::route('frases.index');
});

Route::apiResource('autores', AutoresController::class)
    ->missing(function (Request $request) {
        return Redirect::route('frases.index');
});
Route::apiResource('categorias', CategoriasController::class)
    ->only(['index','show','store', 'update', 'destroy']);

