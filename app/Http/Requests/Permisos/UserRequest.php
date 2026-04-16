<?php

namespace App\Http\Requests\Permisos;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    protected $redirectRoute = 'usuarios.create';

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
        return [
            'nombreUsuario' => 'required|min:3',
            'correoUsuario' => 'required|email|unique:users,email',
            'contrasenaUsuario' => 'required|min:8|same:confirmar_contrasenaUsuario',
            'confirmar_contrasenaUsuario' => 'required',
            'departamento_id' => 'required|exists:departamentos,id',
            'tipo_personal_id' => 'required|exists:tipos_personal,id',
            'cod_estudiante' => 'required|string|max:30',
            'cantidad_horas_totales' => 'required|numeric|min:0',
            'celular' => 'nullable|numeric',
            'telefono_contacto' => 'nullable|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'nombreUsuario.required' => 'El nombre es obligatorio.',
            'nombreUsuario.min' => 'El nombre debe tener al menos :min caracteres.',
            'correoUsuario.required' => 'El correo electronico es obligatorio.',
            'correoUsuario.email' => 'El correo electronico debe ser una direccion de correo valida.',
            'correoUsuario.unique' => 'El correo electronico ya esta en uso.',
            'contrasenaUsuario.required' => 'La contrasena es obligatoria.',
            'contrasenaUsuario.min' => 'La contrasena debe tener al menos :min caracteres.',
            'contrasenaUsuario.same' => 'Las contrasenas no coinciden.',
            'confirmar_contrasenaUsuario.required' => 'Debe confirmar la contrasena.',
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
