<?php

namespace App\Http\Requests\Agenda;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class SedeUpdateRequest extends FormRequest
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
        $id = $this->resolveSedeId();

        return [
            'nombre' => [
                'required',
                'string',
                'min:3',
                'max:150',
                Rule::unique('sedes', 'nombre')->ignore($id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la sede es obligatorio.',
            'nombre.string' => 'El nombre de la sede debe ser texto.',
            'nombre.min' => 'El nombre de la sede debe tener al menos :min caracteres.',
            'nombre.max' => 'El nombre de la sede no puede superar :max caracteres.',
            'nombre.unique' => 'El nombre de la sede ya existe.',
        ];
    }

    private function resolveSedeId(): ?int
    {
        $encryptedId = $this->route('id');

        if (! is_string($encryptedId) || $encryptedId === '') {
            return null;
        }

        return decrypt_id($encryptedId);
    }
}
