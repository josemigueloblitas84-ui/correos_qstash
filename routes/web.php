<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Permisos\PermisosController;
use App\Http\Controllers\Permisos\RolController;
use App\Http\Controllers\Permisos\UserController;
use App\Http\Controllers\FormMultiPasosController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Agenda\DepartamentoController;
use App\Http\Controllers\Agenda\TipoPersonalController;

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
        ? redirect('/formMultiPasos/create')
        : redirect('/login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/formulario', 'formulario')->name('formulario');

    Route::middleware('password.confirm')->group(function () {
        //Solo es para rutas delicadas
    });

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

        Route::get('/{id}/roles', 'editRoles')->name('roles.edit');
        Route::post('/{id}/roles', 'updateRoles')->name('roles.update');


        Route::get('/{id}/permisos-especiales', 'editPermisosEspeciales')->name('permisos.edit');
        Route::post('/{id}/permisos-especiales', 'updatePermisosEspeciales')->name('permisos.update');
    });

    Route::prefix('formMultiPasos')->name('articulos.')->controller(FormMultiPasosController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{articulo}/edit', 'edit')->name('edit');
        Route::put('/{articulo}', 'update')->name('update');
        Route::delete('/{articulo}', 'destroy')->name('destroy');
        Route::get('/agenda-registrada/data', 'agendaData')->name('agenda.data');
    });

    Route::prefix('departamentos')->name('departamentos.')->controller(DepartamentoController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/data', 'data')->name('data');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::put('/{id}', 'update')->name('update');
        Route::patch('/{id}/toggle-status', 'toggleStatus')->name('toggle-status');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    Route::prefix('tipos-personal')->name('tipos-personal.')->controller(TipoPersonalController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/data', 'data')->name('data');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    //Ruta de los Logs
    Route::prefix('logs')->name('logs.')->controller(ActivityLogController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/data', 'data')->name('data');
    });
});


require __DIR__ . '/auth.php';
