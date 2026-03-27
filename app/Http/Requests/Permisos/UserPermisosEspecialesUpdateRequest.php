<?php

namespace App\Http\Requests\Permisos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserPermisosEspecialesUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['string', Rule::exists('permissions', 'name')],
        ];
    }

    public function messages(): array
    {
        return [
            'permisos.array' => 'Los permisos deben enviarse correctamente.',
            'permisos.*.string' => 'Uno de los permisos seleccionados no es valido.',
            'permisos.*.exists' => 'Uno de los permisos seleccionados no existe.',
        ];
    }
}
