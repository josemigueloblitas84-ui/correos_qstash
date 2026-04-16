<?php

namespace App\Http\Requests\Permisos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
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
        $id = $this->route('id');

        return [
            'name' => ['required', 'min:3'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($id),
            ],
            'departamento_id' => ['required', Rule::exists('departamentos', 'id')],
            'tipo_personal_id' => ['required', Rule::exists('tipos_personal', 'id')],
            'cod_estudiante' => ['required', 'string', 'max:30'],
            'cantidad_horas_totales' => ['required', 'numeric', 'min:0'],
            'celular' => ['nullable', 'numeric'],
            'telefono_contacto' => ['nullable', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.min' => 'El nombre debe tener al menos :min caracteres.',
            'email.required' => 'El correo electronico es obligatorio.',
            'email.email' => 'El correo electronico debe ser valido.',
            'email.unique' => 'El correo electronico ya esta en uso.',
            'departamento_id.required' => 'Debe seleccionar un departamento.',
            'departamento_id.exists' => 'El departamento seleccionado no es valido.',
            'tipo_personal_id.required' => 'Debe seleccionar un tipo de personal.',
            'tipo_personal_id.exists' => 'El tipo de personal seleccionado no es valido.',
            'cod_estudiante.required' => 'El código de estudiante es obligatorio.',
            'cod_estudiante.string' => 'El codigo de estudiante debe ser texto.',
            'cod_estudiante.max' => 'El codigo de estudiante no debe superar :max caracteres.',
            'cantidad_horas_totales.numeric' => 'La cantidad de horas totales debe ser numerica.',
            'cantidad_horas_totales.min' => 'La cantidad de horas totales no puede ser negativa.',
            'celular.numeric' => 'El celular debe ser numerico.',
            'telefono_contacto.numeric' => 'El telefono de contacto debe ser numerico.',
        ];
    }
}
