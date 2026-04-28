<?php

namespace App\Http\Requests\Permisos;

use App\Rules\SedePerteneceAInstitucion;
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
            'contrasenaUsuario' => ['nullable', 'string', 'min:8', 'same:confirmar_contrasenaUsuario'],
            'confirmar_contrasenaUsuario' => ['nullable', 'required_with:contrasenaUsuario'],
            'departamento_id' => ['required', Rule::exists('departamentos', 'id')],
            'tipo_personal_id' => ['required', Rule::exists('tipos_personal', 'id')],
            'institucion_id' => ['required', 'integer', Rule::exists('instituciones', 'id')],
            'sede_id' => ['bail', 'required', 'integer', Rule::exists('sedes', 'id'), new SedePerteneceAInstitucion($this->input('institucion_id'))],
            'cod_estudiante' => ['required', 'string', 'max:30'],
            'cantidad_horas_totales' => ['nullable', 'integer', 'min:0', 'digits_between:1,10', 'max:2147483647'],
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
            'telefono_contacto' => ['nullable', 'integer', 'min:0', 'digits_between:1,10', 'max:2147483647'],
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
            'contrasenaUsuario.min' => 'La contrasena debe tener al menos :min caracteres.',
            'contrasenaUsuario.same' => 'Las contrasenas no coinciden.',
            'confirmar_contrasenaUsuario.required_with' => 'Debe confirmar la contrasena para actualizarla.',
            'departamento_id.required' => 'Debe seleccionar un departamento.',
            'departamento_id.exists' => 'El departamento seleccionado no es valido.',
            'tipo_personal_id.required' => 'Debe seleccionar un tipo de personal.',
            'tipo_personal_id.exists' => 'El tipo de personal seleccionado no es valido.',
            'institucion_id.required' => 'Debe seleccionar una institucion.',
            'institucion_id.integer' => 'La institucion seleccionada no es valida.',
            'institucion_id.exists' => 'La institucion seleccionada no existe.',
            'sede_id.required' => 'Debe seleccionar una sede.',
            'sede_id.integer' => 'La sede seleccionada no es valida.',
            'sede_id.exists' => 'La sede seleccionada no existe.',
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
