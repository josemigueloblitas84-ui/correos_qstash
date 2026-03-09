<?php

namespace App\Http\Requests\Permisos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermisoUpdateRequest extends FormRequest
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
        $permiso = $this->route('permiso');

        return [
            'name' => [
                'required',
                'min:3',
                Rule::unique('permissions', 'name')->ignore($permiso->id),
            ],
        ];
    }


    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del permiso es obligatorio.',
            'name.min' => 'El nombre del permiso debe tener al menos :min caracteres.',
            'name.unique' => 'El nombre del permiso ya existe.',
        ];
    }
}
