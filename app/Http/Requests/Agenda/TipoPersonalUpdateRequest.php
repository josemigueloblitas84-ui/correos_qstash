<?php

namespace App\Http\Requests\Agenda;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class TipoPersonalUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! $this->ajax()) {
            throw new HttpResponseException(
                response()->json([
                    'message' => 'No puedes acceder a esta ruta.'
                ], 403)
            );
        }

        return true;
    }

    public function rules(): array
    {
        $id = $this->resolveTipoPersonalId();

        return [
            'tipo' => [
                'required',
                'string',
                'min:3',
                'max:50',
                Rule::unique('tipos_personal', 'tipo')->ignore($id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.required' => 'El tipo es obligatorio.',
            'tipo.min' => 'El tipo debe tener al menos :min caracteres.',
            'tipo.max' => 'El tipo no puede superar :max caracteres.',
            'tipo.unique' => 'Ese tipo ya existe.',
        ];
    }

    private function resolveTipoPersonalId(): ?int
    {
        $encryptedId = $this->route('id');

        if (! is_string($encryptedId) || $encryptedId === '') {
            return null;
        }

        return decrypt_id($encryptedId);
    }
}
