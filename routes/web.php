<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Permisos\PermisosController;
use App\Http\Controllers\Permisos\RolController;
use App\Http\Controllers\Permisos\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//Prueba de Admin LTE, borrar luego
Route::get('/prueba', function () {
    return view('plantilla.app');
});

Route::get('/', function () {
    return auth()->check()
        ? redirect('/dashboard')
        : redirect('/login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/formulario', function () {
    return view('formulario');
})->middleware(['auth', 'verified'])->name('formulario');

Route::middleware('auth', 'password.confirm')->group(function () {
    Route::get('/profile/show', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit'); // Ya no es necesario, se usaahora el show
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // permisos
    Route::get('/permisos', [PermisosController::class, 'index'])->name('permisos.index');
    Route::get('/permisos/create', [PermisosController::class, 'create'])->name('permisos.create');
    Route::post('/permisos', [PermisosController::class, 'store'])->name('permisos.store');
    Route::get('/permisos/{id}/edit', [PermisosController::class, 'edit'])->name('permisos.edit');
    Route::post('/permisos/{id}', [PermisosController::class, 'update'])->name('permisos.update');
    Route::delete('/permisos', [PermisosController::class, 'destroy'])->name('permisos.destroy');

    // Roles
    Route::get('/roles', [RolController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RolController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RolController::class, 'store'])->name('roles.store');
    Route::get('/roles/{id}/edit', [RolController::class, 'edit'])->name('roles.edit');
    Route::post('/roles/{id}', [RolController::class, 'update'])->name('roles.update');
    Route::delete('/roles', [RolController::class, 'destroy'])->name('roles.destroy');

    // Usuarios
    Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/create', [UserController::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store');
    Route::get('/usuarios/{id}/edit', [UserController::class, 'edit'])->name('usuarios.edit');
    Route::post('/usuarios/{id}', [UserController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios', [UserController::class, 'destroy'])->name('usuarios.destroy');
});

require __DIR__ . '/auth.php';
