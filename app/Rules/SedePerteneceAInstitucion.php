<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class SedePerteneceAInstitucion implements ValidationRule
{
    public function __construct(
        protected mixed $institucionId
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($this->institucionId) || empty($value)) {
            return;
        }

        $exists = DB::table('institucion_sede')
            ->where('institucion_id', (int) $this->institucionId)
            ->where('sede_id', (int) $value)
            ->exists();

        if (! $exists) {
            $fail('La sede seleccionada no pertenece a la institucion elegida.');
        }
    }
}
