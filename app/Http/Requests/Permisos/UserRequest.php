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
        ];
    }
}
