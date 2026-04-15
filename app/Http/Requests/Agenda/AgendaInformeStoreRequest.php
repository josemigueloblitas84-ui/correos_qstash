<?php

namespace App\Http\Requests\Agenda;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class AgendaInformeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date'],

            'programadas' => ['nullable', 'array'],
            'programadas.*.agenda_actividad_id' => ['required', 'integer', 'exists:agenda_actividades,id'],
            'programadas.*.agenda_id' => ['required', 'integer', 'exists:agendas,id'],
            'programadas.*.actividad' => ['required', 'string'],
            'programadas.*.departamento_id' => ['required', 'integer', 'exists:departamentos,id'],
            'programadas.*.tipo_actividad' => ['required', Rule::in(['D', 'S'])],
            'programadas.*.estado' => ['nullable', 'boolean'],
            'programadas.*.detalle_estado' => ['nullable', 'string'],

            'no_programadas' => ['nullable', 'array'],
            'no_programadas.*.agenda_id' => ['required', 'integer', 'exists:agendas,id'],
            'no_programadas.*.actividad' => ['required', 'string'],
            'no_programadas.*.departamento_id' => ['required', 'integer', 'exists:departamentos,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.required' => 'La fecha es obligatoria.',
            'programadas.*.agenda_actividad_id.exists' => 'La actividad programada no existe.',
            'programadas.*.departamento_id.required' => 'Una actividad programada no tiene equipo asignado.',
            'programadas.*.departamento_id.exists' => 'El equipo de la actividad programada no es válido.',
            'no_programadas.*.agenda_id.required' => 'La agenda es obligatoria para actividades no programadas.',
            'no_programadas.*.actividad.required' => 'La actividad no programada es obligatoria.',
            'no_programadas.*.departamento_id.required' => 'Debe seleccionar un equipo.',
            'no_programadas.*.departamento_id.exists' => 'El equipo seleccionado no es válido.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        dd($validator->errors()->toArray(), $this->all());
    }
}
