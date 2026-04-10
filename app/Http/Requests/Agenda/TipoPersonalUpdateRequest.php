<?php

namespace App\Http\Requests\Agenda;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TipoPersonalUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

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
}
