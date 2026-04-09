<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormularioController;
use App\Http\Controllers\Api\AuthController;

use App\Http\Controllers\Api\PermisoController;
use App\Http\Controllers\Api\RolController;
use App\Http\Controllers\Api\UserController;


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

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('jwt')->group(function(){
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', RolController::class);
    Route::apiResource('permisos', PermisoController::class);

    Route::put('users/{user}/permisosespeciales', [UserController::class, 'updatePermisosEspeciales']);
});

Route::post('/qstash/enviar-email', [FormularioController::class, 'enviarEmail'])->middleware('qstash');
