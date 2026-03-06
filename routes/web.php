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
*/

Route::get('/prueba', function () {
    return view('plantilla.app');
});

Route::get('/', function () {
    return auth()->check()
        ? redirect('/dashboard')
        : redirect('/login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/formulario', 'formulario')->name('formulario');

    Route::middleware('password.confirm')->group(function () {
        Route::controller(ProfileController::class)->group(function () {
            Route::get('/profile/show', 'show')->name('profile.show');
            Route::get('/profile', 'edit')->name('profile.edit');
            Route::patch('/profile', 'update')->name('profile.update');
            Route::delete('/profile', 'destroy')->name('profile.destroy');
        });

        Route::prefix('permisos')->name('permisos.')->controller(PermisosController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::post('/{id}', 'update')->name('update');
            Route::delete('/', 'destroy')->name('destroy');
        });

        Route::prefix('roles')->name('roles.')->controller(RolController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::post('/{id}', 'update')->name('update');
            Route::delete('/', 'destroy')->name('destroy');
        });

        Route::prefix('usuarios')->name('usuarios.')->controller(UserController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::post('/{id}', 'update')->name('update');
            Route::delete('/', 'destroy')->name('destroy');

            Route::get('/{id}/permisos-especiales', 'editPermisosEspeciales')->name('permisos.edit');
            Route::post('/{id}/permisos-especiales', 'updatePermisosEspeciales')->name('permisos.update');
        });
    });
});

require __DIR__ . '/auth.php';
