<?php

namespace App\Services\Agenda;

use App\Exceptions\AgendaActividadException;
use Illuminate\Support\Facades\DB;
use Throwable;

class AgendaActividadService
{
    public function store(array $data, int $userId): int
    {
        try {
            return DB::transaction(function () use ($data, $userId) {
                $agenda = DB::table('agendas')
                    ->where('id', $data['agenda_id'])
                    ->first();

                if (! $agenda) {
                    throw new AgendaActividadException('La agenda no existe.', 404);
                }

                $fechaDel = $data['tipo_actividad'] === 'S'
                    ? $data['fecha_del']
                    : null;

                $fechaHasta = $data['tipo_actividad'] === 'S'
                    ? $data['fecha_hasta']
                    : null;

                $horaDesde = $data['tipo_actividad'] === 'D'
                    ? $data['hora_desde_actividad']
                    : null;

                $horaHasta = $data['tipo_actividad'] === 'D'
                    ? $data['hora_hasta_actividad']
                    : null;

                return DB::table('agenda_actividades')->insertGetId([
                    'agenda_id' => $agenda->id,
                    'fecha_del' => $fechaDel,
                    'fecha_hasta' => $fechaHasta,
                    'hora_desde_actividad' => $horaDesde,
                    'hora_hasta_actividad' => $horaHasta,
                    'archivo' => null,
                    'usuario_id' => $userId,
                    'fecha_registro' => now()->toDateString(),
                    'actividad' => trim($data['actividad']),
                    'observacion' => null,
                    'departamento_id' => $data['departamento_id'],
                    'tipo_actividad' => $data['tipo_actividad'],
                    'estado' => 0,
                    'detalle_estado' => null,
                    'actividad_principal' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        } catch (AgendaActividadException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            throw new AgendaActividadException('Ocurrió un error al registrar la actividad.', 500);
        }
    }
}
