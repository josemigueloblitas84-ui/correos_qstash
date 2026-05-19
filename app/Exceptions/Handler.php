<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (UnauthorizedException $e, $request) {
            return response()->json(['message' => 'No tienes permisos para realizar esta accion'], 403);
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Recurso no encontrado o identificador invalido.',
                ], 404);
            }

            return auth()->check()
                ? redirect(tenancy()->initialized ? '/dashboard' : '/instituciones')->with('error', 'La ruta o el identificador no es valido.')
                : redirect('/login');
        });
    }
}
