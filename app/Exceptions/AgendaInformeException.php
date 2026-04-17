<?php

namespace App\Exceptions;

use Exception;

class AgendaInformeException extends Exception
{
    public function __construct(string $message = 'No se pudo guardar el informe.', protected int $status = 422)
    {
        parent::__construct($message, $status);
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
            ], $this->status);
        }

        return redirect()
            ->back()
            ->withInput()
            ->withErrors([
                'informe' => $this->getMessage(),
            ]);
    }
}
