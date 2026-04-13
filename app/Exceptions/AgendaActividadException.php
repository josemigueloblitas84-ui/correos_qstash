<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class AgendaActividadException extends Exception
{
    public function __construct(string $message = 'No se pudo registrar la actividad.', protected int $status = 422)
    {
        parent::__construct($message, $status);
    }

    public function render($request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], $this->status);
    }
}
