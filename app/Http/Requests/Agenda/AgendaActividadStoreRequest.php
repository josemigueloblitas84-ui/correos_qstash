<?php

namespace App\Http\Requests\Agenda;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgendaActividadStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'agenda_id' => ['required', 'integer', 'exists:agendas,id'],
            'actividad' => ['required', 'string', 'max:255'],
            'departamento_id' => ['required', 'integer', 'exists:departamentos,id'],
            'tipo_actividad' => ['required', Rule::in(['D', 'S'])],
            'fecha_del' => ['nullable', 'date', Rule::requiredIf($this->input('tipo_actividad') === 'S')],
            'fecha_hasta' => ['nullable', 'date', Rule::requiredIf($this->input('tipo_actividad') === 'S')],
            'hora_desde_actividad' => ['nullable', 'regex:/^\d{2}:\d{2}$/', Rule::requiredIf($this->input('tipo_actividad') === 'D')],
            'hora_hasta_actividad' => ['nullable', 'regex:/^\d{2}:\d{2}$/', Rule::requiredIf($this->input('tipo_actividad') === 'D')],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('tipo_actividad') === 'S') {
                $fechaDel = $this->input('fecha_del');
                $fechaHasta = $this->input('fecha_hasta');

                if ($fechaDel && $fechaHasta && $fechaHasta < $fechaDel) {
                    $validator->errors()->add('fecha_hasta', 'La fecha hasta debe ser mayor o igual a la fecha desde.');
                }

                if ($fechaDel && $fechaHasta) {
                    $desde = Carbon::parse($fechaDel);
                    $hasta = Carbon::parse($fechaHasta);

                    if (! $desde->isSameMonth($hasta)) {
                        $validator->errors()->add('fecha_hasta', 'La fecha hasta debe pertenecer al mismo mes que la fecha desde.');
                    }
                }
            }

            if ($this->input('tipo_actividad') === 'D') {
                $horaDesde = $this->input('hora_desde_actividad');
                $horaHasta = $this->input('hora_hasta_actividad');

                if ($horaDesde && $horaHasta && $horaHasta <= $horaDesde) {
                    $validator->errors()->add('hora_hasta_actividad', 'La hora hasta debe ser mayor a la hora desde.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'agenda_id.required' => 'La agenda es obligatoria.',
            'agenda_id.exists' => 'La agenda seleccionada no existe.',
            'actividad.required' => 'La actividad es obligatoria.',
            'departamento_id.required' => 'Debe seleccionar un equipo.',
            'departamento_id.exists' => 'El equipo seleccionado no es válido.',
            'tipo_actividad.required' => 'Debe seleccionar el tipo de actividad.',
            'tipo_actividad.in' => 'El tipo de actividad no es válido.',
            'fecha_del.required' => 'La fecha desde es obligatoria para actividad semanal.',
            'fecha_hasta.required' => 'La fecha hasta es obligatoria para actividad semanal.',
            'hora_desde_actividad.required' => 'La hora desde es obligatoria para actividad diaria.',
            'hora_hasta_actividad.required' => 'La hora hasta es obligatoria para actividad diaria.',
            'hora_desde_actividad.regex' => 'La hora desde no tiene un formato válido.',
            'hora_hasta_actividad.regex' => 'La hora hasta no tiene un formato válido.',
        ];
    }
}
