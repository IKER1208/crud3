<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AutorController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EditorialController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\GeneroController;
use App\Http\Controllers\LibroController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\IdiomaController;
use App\Http\Controllers\AuthController;

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

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->group(function () {
    Route::resource('autor', AutorController::class);
    Route::resource('categoria', CategoriaController::class);
    Route::resource('cliente', ClienteController::class);
    Route::resource('editorial', EditorialController::class);
    Route::resource('empleado', EmpleadoController::class);
    Route::resource('genero', GeneroController::class);
    Route::resource('idioma', IdiomaController::class);
    Route::resource('libro', LibroController::class);
    Route::resource('prestamo', PrestamoController::class);
    Route::resource('sucursal', SucursalController::class);
});
