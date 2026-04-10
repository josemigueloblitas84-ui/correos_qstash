<?php

namespace App\Http\Requests\Agenda;

use Illuminate\Foundation\Http\FormRequest;

class TipoPersonalStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo' => 'required|string|min:3|max:50|unique:tipos_personal,tipo',
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
}
