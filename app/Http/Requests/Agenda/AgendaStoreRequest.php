<?php

namespace App\Http\Requests\Agenda;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgendaStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('ver agenda') ?? false;
    }

    protected function prepareForValidation():void
    {
        $user =$this->user();

        if($user && ! $user->hasRole('SuperAdministrador')){
            $this->merge([
                'cod_unidad' => $user->departamento_id,
                'cod_solicitante' => $user->id,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date'],
            'cod_unidad' => ['required', 'integer', 'exists:departamentos,id'],
            'cod_solicitante' => ['required', 'integer', 'exists:users,id'],
            'fecha_desde' => ['required', 'date'],
            'fecha_hasta' => ['required', 'date', 'after_or_equal:fecha_desde'],
            'hora_inicio_hora' => ['required', Rule::in($this->hourOptions())],
            'hora_inicio_minuto' => ['required', Rule::in($this->minuteOptions())],
            'hora_fin_hora' => ['required', Rule::in($this->hourOptions())],
            'hora_fin_minuto' => ['required', Rule::in($this->minuteOptions())],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $fechaDesde = $this->input('fecha_desde');
            $fechaHasta = $this->input('fecha_hasta');

            if($fechaDesde && $fechaHasta){
                $desde = Carbon::parse($fechaDesde);
                $hasta = Carbon::parse($fechaHasta);

                if(! $desde->isSameMonth($hasta)){
                    $validator->errors()->add(
                        'fecha_hasta',
                        'La fecha debe pertenecer al mismo mes que la fecha inicial.'
                    );
                }
            }

            $horaInicio = $this->buildTime('hora_inicio_hora', 'hora_inicio_minuto');
            $horaFin = $this->buildTime('hora_fin_hora', 'hora_fin_minuto');

            if ($horaInicio && $horaFin && $horaFin <= $horaInicio) {
                $validator->errors()->add(
                    'hora_fin_hora',
                    'La hora final debe ser mayor a la hora de inicio.'
                );
            }
        });
    }
    public function messages(): array
    {
        return [
            'fecha.required' => 'La fecha es obligatoria.',
            'cod_unidad.required' => 'Debe seleccionar un departamento o unidad.',
            'cod_unidad.exists' => 'El departamento seleccionado no es valido.',
            'cod_solicitante.required' => 'Debe seleccionar un solicitante.',
            'cod_solicitante.exists' => 'El solicitante seleccionado no es valido.',
            'fecha_desde.required' => 'La fecha inicial es obligatoria.',
            'fecha_hasta.required' => 'La fecha final es obligatoria.',
            'fecha_hasta.after_or_equal' => 'La fecha final debe ser mayor o igual a la fecha inicial.',
            'hora_inicio_hora.required' => 'Debe seleccionar la hora de inicio.',
            'hora_inicio_minuto.required' => 'Debe seleccionar los minutos de inicio.',
            'hora_fin_hora.required' => 'Debe seleccionar la hora final.',
            'hora_fin_minuto.required' => 'Debe seleccionar los minutos finales.',
        ];
    }

    private function hourOptions(): array
    {
        return array_map(
            fn ($hour) => str_pad((string) $hour, 2, '0', STR_PAD_LEFT),
            range(0, 23)
        );
    }

    private function minuteOptions(): array
    {
        return ['00', '15', '30', '45'];
    }

    private function buildTime(string $hourField, string $minuteField): ?string
    {
        $hour = $this->input($hourField);
        $minute = $this->input($minuteField);

        if ($hour === null || $minute === null || $hour === '' || $minute === '') {
            return null;
        }

        return $hour . ':' . $minute;
    }
}
