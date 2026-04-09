<?php

namespace App\Http\Requests\Agenda;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartamentoUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'nombre_depa' => [
                'required',
                'string',
                'min:3',
                'max:100',
                Rule::unique('departamentos', 'nombre_depa')->ignore($id),
            ],
            'estado_depa' => 'required|in:activo,inactivo',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_depa.required' => 'El nombre del departamento es obligatorio.',
            'nombre_depa.min' => 'El nombre del departamento debe tener al menos :min caracteres.',
            'nombre_depa.max' => 'El nombre del departamento no puede superar :max caracteres.',
            'nombre_depa.unique' => 'El nombre del departamento ya existe.',

            'estado_depa.required' => 'El estado del departamento es obligatorio.',
            'estado_depa.in' => 'El estado seleccionado no es válido.',
        ];
    }
}
