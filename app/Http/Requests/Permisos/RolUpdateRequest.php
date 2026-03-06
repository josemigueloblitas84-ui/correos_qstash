<?php

namespace App\Http\Requests\Permisos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RolUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $roleId = $this->route('id');
        return [
            'name' => [
                'required',
                'min:3',
                Rule::unique('roles', 'name')->ignore($roleId, 'id'),
            ],
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permissions,name',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.min' => 'El nombre del rol debe tener al menos :min caracteres.',
            'name.unique' => 'El nombre del rol ya existe.',
            'permisos.array' => 'Los permisos deben enviarse como lista.',
            'permisos.*.exists' => 'Uno de los permisos seleccionados no existe.',
        ];
    }
}
