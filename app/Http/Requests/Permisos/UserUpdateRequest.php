<?php

namespace App\Http\Requests\Permisos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->resolveUserId();

        return [
            'name' => ['required', 'min:3'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($id),
            ],
            'contrasenaUsuario' => [
                'nullable',
                'string',
                'min:8',
                'regex:/^(?=.*\p{Lu})(?=.*\p{Ll})(?=.*\d)(?=.*[\p{P}\p{S}])[\p{Latin}\d\p{P}\p{S}]+$/u',
                'same:confirmar_contrasenaUsuario',
            ],
            'confirmar_contrasenaUsuario' => ['nullable', 'required_with:contrasenaUsuario'],
            'departamento_id' => ['required', Rule::exists('departamentos', 'id')],
            'tipo_personal_id' => ['required', Rule::exists('tipos_personal', 'id')],
            'cod_estudiante' => [
                'required',
                'string',
                'max:30',
                Rule::unique('users', 'cod_estudiante')->ignore($id),
            ],
            'cantidad_horas_totales' => ['nullable', 'integer', 'min:0', 'digits_between:1,10', 'max:2147483647'],
            'celular' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^\d+$/',
                'phone',
            ],
            'celular_country' => ['nullable', 'required_with:celular', 'string', 'size:2'],
            'telefono_contacto' => ['nullable', 'string', 'max:30', 'regex:/^\d+$/', 'phone'],
            'telefono_contacto_country' => ['nullable', 'required_with:telefono_contacto', 'string', 'size:2'],
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
            'contrasenaUsuario.min' => 'La contraseña debe tener al menos :min caracteres.',
            'contrasenaUsuario.regex' => 'La contraseña solo puede contener letras latinas, números y símbolos, e incluir al menos una mayúscula, una minúscula, un número y un símbolo.',
            'contrasenaUsuario.same' => 'Las contraseñas no coinciden.',
            'confirmar_contrasenaUsuario.required_with' => 'Debe confirmar la contraseña para actualizarla.',
            'departamento_id.required' => 'Debe seleccionar un departamento.',
            'departamento_id.exists' => 'El departamento seleccionado no es valido.',
            'tipo_personal_id.required' => 'Debe seleccionar un tipo de personal.',
            'tipo_personal_id.exists' => 'El tipo de personal seleccionado no es valido.',
            'cod_estudiante.required' => 'El código de estudiante es obligatorio.',
            'cod_estudiante.string' => 'El codigo de estudiante debe ser texto.',
            'cod_estudiante.max' => 'El codigo de estudiante no debe superar :max caracteres.',
            'cod_estudiante.unique' => 'El codigo de estudiante ya esta en uso.',
            'cantidad_horas_totales.integer' => 'La cantidad de horas totales debe ser un numero entero.',
            'cantidad_horas_totales.min' => 'La cantidad de horas totales no puede ser negativa.',
            'cantidad_horas_totales.digits_between' => 'La cantidad de horas totales no puede superar :max digitos.',
            'cantidad_horas_totales.max' => 'La cantidad de horas totales no puede exceder el limite permitido.',
            'celular.max' => 'El celular no puede superar :max caracteres.',
            'celular.regex' => 'El celular solo puede contener numeros.',
            'celular.phone' => 'El celular debe ser un numero valido para el pais seleccionado.',
            'celular_country.required_with' => 'Debe seleccionar el pais del celular.',
            'celular_country.size' => 'El pais del celular debe tener un codigo ISO de 2 letras.',
            'telefono_contacto.max' => 'El telefono de contacto no puede superar :max caracteres.',
            'telefono_contacto.regex' => 'El telefono de contacto solo puede contener numeros.',
            'telefono_contacto.phone' => 'El telefono de contacto debe ser un numero valido para el pais seleccionado.',
            'telefono_contacto_country.required_with' => 'Debe seleccionar el pais del telefono de contacto.',
            'telefono_contacto_country.size' => 'El pais del telefono de contacto debe tener un codigo ISO de 2 letras.',
        ];
    }

    private function resolveUserId(): ?int
    {
        $encryptedId = $this->route('id');

        if (! is_string($encryptedId) || $encryptedId === '') {
            return null;
        }

        return decrypt_id($encryptedId);
    }
}
