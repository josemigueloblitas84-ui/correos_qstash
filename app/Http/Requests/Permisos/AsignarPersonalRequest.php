<?php

namespace App\Http\Requests\Permisos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AsignarPersonalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'usuarios_asignados' => ['nullable', 'array'],
            'usuarios_asignados.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id'),
                // Rule::notIn([(int) $this->route('id')]), Con esto el usuario no puede asignarse a si mismo
            ],
            'validador' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'usuarios_asignados.*.exists' => 'Uno de los usuarios seleccionados no existe.',
            'usuarios_asignados.*.distinct' => 'No repitas usuarios en la asignacion.',
        ];
    }
}
