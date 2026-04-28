<?php

namespace App\Http\Requests\Agenda;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class InstitucionSedesUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! $this->ajax()) {
            throw new HttpResponseException(
                response()->json([
                    'message' => 'No puedes acceder a esta ruta.',
                ], 403)
            );
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'sedes' => ['nullable', 'array'],
            'sedes.*' => ['integer', Rule::exists('sedes', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'sedes.array' => 'Las sedes deben enviarse como lista.',
            'sedes.*.integer' => 'Una de las sedes seleccionadas no es valida.',
            'sedes.*.exists' => 'Una de las sedes seleccionadas no existe.',
        ];
    }
}
