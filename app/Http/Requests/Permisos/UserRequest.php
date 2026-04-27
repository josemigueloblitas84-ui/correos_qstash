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
            'contrasenaUsuario' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*\p{Lu})(?=.*\p{Ll})(?=.*\d)(?=.*[\p{P}\p{S}])[\p{Latin}\d\p{P}\p{S}]+$/u',
                'same:confirmar_contrasenaUsuario',
            ],
            'confirmar_contrasenaUsuario' => 'required',
            'departamento_id' => 'required|exists:departamentos,id',
            'tipo_personal_id' => 'required|exists:tipos_personal,id',
            'cod_estudiante' => 'required|string|max:30',
            'cantidad_horas_totales' => 'nullable|integer|min:0|digits_between:1,10|max:2147483647',
            'celular' => [
                'nullable',
                'integer',
                'min:0',
                'digits_between:1,12',
                function ($attribute, $value, $fail) {
                    if ($value !== null && (int) $value > 2147483647) {
                        $fail('El celular no puede superar 12 digitos y debe estar dentro del limite permitido por el sistema.');
                    }
                },
            ],
            'telefono_contacto' => 'nullable|integer|min:0|digits_between:1,10|max:2147483647',
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
            'contrasenaUsuario.required' => 'La contraseña es obligatoria.',
            'contrasenaUsuario.min' => 'La contraseña debe tener al menos :min caracteres.',
            'contrasenaUsuario.regex' => 'La contraseña solo puede contener letras latinas, números y símbolos, e incluir al menos una mayúscula, una minúscula, un número y un símbolo.',
            'contrasenaUsuario.same' => 'Las contraseñas no coinciden.',
            'confirmar_contrasenaUsuario.required' => 'Debe confirmar la contraseña.',
            'departamento_id.required' => 'Debe seleccionar un departamento.',
            'departamento_id.exists' => 'El departamento seleccionado no es valido.',
            'tipo_personal_id.required' => 'Debe seleccionar un tipo de personal.',
            'tipo_personal_id.exists' => 'El tipo de personal seleccionado no es valido.',
            'cod_estudiante.required' => 'El código de estudiante es obligatorio.',
            'cod_estudiante.string' => 'El codigo de estudiante debe ser texto.',
            'cod_estudiante.max' => 'El codigo de estudiante no debe superar :max caracteres.',
            'cantidad_horas_totales.integer' => 'La cantidad de horas totales debe ser un numero entero.',
            'cantidad_horas_totales.min' => 'La cantidad de horas totales no puede ser negativa.',
            'cantidad_horas_totales.digits_between' => 'La cantidad de horas totales no puede superar :max digitos.',
            'cantidad_horas_totales.max' => 'La cantidad de horas totales no puede exceder el limite permitido.',
            'celular.integer' => 'El celular debe ser un numero entero.',
            'celular.min' => 'El celular no puede ser negativo.',
            'celular.digits_between' => 'El celular no puede superar 12 digitos.',
            'telefono_contacto.integer' => 'El telefono de contacto debe ser un numero entero.',
            'telefono_contacto.min' => 'El telefono de contacto no puede ser negativo.',
            'telefono_contacto.digits_between' => 'El telefono de contacto no puede superar :max digitos.',
            'telefono_contacto.max' => 'El telefono de contacto no puede exceder el limite permitido.',
        ];
    }
}
