<?php

namespace App\Http\Requests\Agenda;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Validator;
use Throwable;

class InstitucionAdministradorStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! $this->ajax()) {
            throw new HttpResponseException(
                response()->json([
                    'message' => 'No puedes acceder a esta ruta.',
                ], 403)
            );
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del administrador es obligatorio.',
            'name.min' => 'El nombre debe tener al menos :min caracteres.',
            'name.max' => 'El nombre no puede superar :max caracteres.',
            'email.required' => 'El correo del administrador es obligatorio.',
            'email.email' => 'El correo debe ser una direccion valida.',
            'email.max' => 'El correo no puede superar :max caracteres.',
            'password.required' => 'La contrasena es obligatoria.',
            'password.min' => 'La contrasena debe tener al menos :min caracteres.',
            'password.confirmed' => 'Las contrasenas no coinciden.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $tenant = Tenant::query()->find((string) $this->route('id'));

                if (! $tenant) {
                    $validator->errors()->add('email', 'La institucion no existe.');
                    return;
                }

                try {
                    $tenant->run(function () use ($validator) {
                        if (! Schema::hasTable('users')) {
                            $validator->errors()->add('email', 'La base de datos de la institucion aun no esta preparada.');
                            return;
                        }

                        if (User::query()->where('email', $this->input('email'))->exists()) {
                            $validator->errors()->add('email', 'Ya existe un usuario con ese correo en esta institucion.');
                        }
                    });
                } catch (Throwable) {
                    $validator->errors()->add('email', 'No se pudo conectar con la base de datos de la institucion.');
                }
            },
        ];
    }
}
