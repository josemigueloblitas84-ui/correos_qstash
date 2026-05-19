<?php

use App\Http\Controllers\Agenda\InstitucionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central Routes
|--------------------------------------------------------------------------
|
| These routes run on central domains only. The central app is responsible
| for managing institutions/tenants and their domains.
|
*/

$centralRoutes = function () {
    Route::get('/', function () {
        return auth()->check()
            ? redirect('/instituciones')
            : redirect('/login');
    });

    Route::get('/dashboard', function () {
        return redirect('/instituciones');
    })->middleware(['auth', 'verified']);

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::prefix('instituciones')->name('instituciones.')->controller(InstitucionController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'data')->name('data');
            Route::post('/', 'store')->name('store');
            Route::post('/{id}/conectar', 'connect')->name('connect');
            Route::post('/{id}/administrador', 'storeAdministrator')->name('administrador.store');
            Route::get('/{id}', 'show')->name('show');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(ProfileController::class)->group(function () {
            Route::get('/profile/show', 'show')->name('central.profile.show');
            Route::get('/profile', 'edit')->name('central.profile.edit');
            Route::patch('/profile', 'update')->name('central.profile.update');
            Route::delete('/profile', 'destroy')->name('central.profile.destroy');
        });
    });

    require __DIR__ . '/auth.php';
};

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group($centralRoutes);
}
