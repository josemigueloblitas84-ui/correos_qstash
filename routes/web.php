<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ConfiguracionSistemaController;
use App\Http\Controllers\Permisos\PermisosController;
use App\Http\Controllers\Permisos\RolController;
use App\Http\Controllers\Permisos\UserController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Agenda\DepartamentoController;
use App\Http\Controllers\Agenda\TipoPersonalController;
use App\Http\Controllers\Agenda\InformeController;
use App\Http\Controllers\Reportes\ReporteAgendaInformeController;
use App\Http\Controllers\Certificado\CertificadoPlantillaController;

/*
|--------------------------------------------------------------------------
| Web Routes Oblitas
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

Route::get('/certificados/verificar/{hash}', [CertificadoPlantillaController::class, 'verifyCertificate'])
    ->name('certificados.verificar');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/control-horas/preview', [DashboardController::class, 'controlHorasPreview'])
        ->name('dashboard.control-horas.preview');
    Route::view('/formulario', 'formulario')->name('formulario');

    Route::middleware('password.confirm')->group(function () {
        // Solo es para rutas delicadas
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
        Route::delete('/', 'destroy')->name('destroy');
    });

    Route::prefix('configuracion-sistema')->name('configuracion-sistema.')->controller(ConfiguracionSistemaController::class)->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::post('/', 'update')->name('update');
    });

    Route::prefix('usuarios')->name('usuarios.')->controller(UserController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::post('/{id}', 'update')->name('update');
        Route::delete('/', 'destroy')->name('destroy');
        Route::patch('/{id}/estado', 'toggleStatus')->name('toggle-status');

        Route::get('/{id}/roles', 'editRoles')->name('roles.edit');
        Route::post('/{id}/roles', 'updateRoles')->name('roles.update');

        Route::get('/{id}/permisos-especiales', 'editPermisosEspeciales')->name('permisos.edit');
        Route::post('/{id}/permisos-especiales', 'updatePermisosEspeciales')->name('permisos.update');

        Route::get('/{id}/personal-asignado', 'editPersonalAsignado')->name('personal.edit');
        Route::post('/{id}/personal-asignado', 'updatePersonalAsignado')->name('personal.update');

    });

    Route::prefix('agenda')->name('agenda.')->controller(AgendaController::class)->group(function () {
        Route::get('/usuarios-por-departamento/{id}', 'usuariosPorDepartamento')->name('usuarios.departamento');
        Route::get('/actividades/autocomplete', 'actividadAutocomplete')->name('actividades.autocomplete');
        Route::get('/actividades/data', 'agendaActividadesData')->name('actividades.data');
        Route::get('/actividades/{id}', 'showActividad')->name('actividades.show');
        Route::get('/registradas/data', 'agendaData')->name('registradas.data');
        Route::get('/registradas/{id}', 'showAgenda')->name('registradas.show');
        Route::get('/registradas/{id}/preview', 'previewAgenda')->name('registradas.preview');
        Route::patch('/registradas/{id}/send', 'sendAgenda')->name('registradas.send');
        Route::delete('/registradas/{id}', 'destroyAgenda')->name('registradas.destroy');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::post('/actividades', 'storeActividad')->name('actividades.store');
        Route::put('/actividades/{id}', 'updateActividad')->name('actividades.update');
        Route::delete('/actividades/{id}', 'destroyActividad')->name('actividades.destroy');
        Route::get('/{agenda}/edit', 'edit')->name('edit');
        Route::put('/{agenda}', 'update')->name('update');
        Route::delete('/{agenda}', 'destroy')->name('destroy');
    });

    Route::prefix('departamentos')->name('departamentos.')->controller(DepartamentoController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/data', 'data')->name('data');
        Route::post('/', 'store')->name('store');
        Route::put('/{id}', 'update')->name('update');
        Route::get('/{id}', 'show')->name('show');
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

    // Ruta de los Logs
    Route::prefix('logs')->name('logs.')->controller(ActivityLogController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/data', 'data')->name('data');
    });

    Route::prefix('informe-agenda')->name('informe-agenda.')->controller(InformeController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::post('/no-programada', 'storeNoProgramada')->name('no-programada.store');
    });

    Route::prefix('reporte-agenda-informe')->name('reporte-agenda-informe.')->controller(ReporteAgendaInformeController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/data', 'data')->name('data');
        Route::get('/data-informe', 'dataInforme')->name('data-informe');
        Route::get('/agenda/{id}/preview', 'previewAgenda')->name('agenda.preview');
        Route::get('/informe/preview', 'previewInforme')->name('informe.preview');
        Route::post('/informe/validar', 'validarInforme')->name('informe.validar');
    });

    Route::prefix('certificados')->name('certificados.')->controller(CertificadoPlantillaController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/qr-preview', 'previewQr')->name('qr.preview');
        Route::post('/plantillas', 'store')->name('plantillas.store');
        Route::get('/plantillas/{archivo}', 'show')->name('plantillas.show');
        Route::get('/plantillas/{archivo}/edit', 'edit')->name('plantillas.edit');
        Route::get('/plantillas/{archivo}/preview', 'previewCertificate')->name('plantillas.preview');
        Route::post('/plantillas/{archivo}/estructura', 'saveStructure')->name('plantillas.estructura.store');
    });

    Route::get('/mis-certificados', [CertificadoPlantillaController::class, 'indexUserCertificates'])
        ->middleware('permission:descargar certificado')
        ->name('mis-certificados.index');
    Route::get('/mis-certificados/{archivo}/emitir', [CertificadoPlantillaController::class, 'showGenerateCertificatePage'])
        ->middleware('permission:descargar certificado')
        ->name('mis-certificados.emitir');
    Route::get('/mis-certificados/{archivo}/payload', [CertificadoPlantillaController::class, 'downloadMyCertificate'])
        ->middleware('permission:descargar certificado')
        ->name('mis-certificados.payload');
    Route::post('/mis-certificados/emisiones/{emitido}/archivo', [CertificadoPlantillaController::class, 'storeIssuedCertificateFile'])
        ->middleware('permission:descargar certificado')
        ->name('mis-certificados.emitidos.archivo.store');
    Route::get('/mis-certificados/emisiones/{emitido}/descargar-pdf', [CertificadoPlantillaController::class, 'showIssuedCertificateDownloadPage'])
        ->middleware('permission:descargar certificado')
        ->name('mis-certificados.emitidos.descargar');
    Route::get('/mis-certificados/emisiones/{emitido}/payload-descarga', [CertificadoPlantillaController::class, 'getIssuedCertificateDownloadPayload'])
        ->middleware('permission:descargar certificado')
        ->name('mis-certificados.emitidos.payload');
});

require __DIR__ . '/auth.php';
