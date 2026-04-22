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
        $id = $this->resolveRolId();

        return [
            'name' => [
                'required',
                'min:3',
                Rule::unique('roles', 'name')->ignore($id, 'id'),
            ],
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['string', 'exists:permissions,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.min' => 'El nombre del rol debe tener al menos :min caracteres.',
            'name.unique' => 'El nombre del rol ya existe.',
            'permisos.array' => 'Los permisos enviados no son válidos.',
            'permisos.*.exists' => 'Uno de los permisos seleccionados no existe.',
        ];
    }

    private function resolveRolId(): ?int
    {
        $encryptedId = $this->route('id');

        if (! is_string($encryptedId) || $encryptedId === '') {
            return null;
        }

        return decrypt_id($encryptedId);
    }
}
