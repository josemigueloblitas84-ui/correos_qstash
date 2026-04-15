<?php

namespace App\Services\Agenda;

use App\Exceptions\AgendaActividadException;
use Illuminate\Support\Facades\DB;
use Throwable;

class AgendaActividadService
{
    public function findById(int $id)
    {
        return DB::table('agenda_actividades')
            ->where('id', $id)
            ->first();
    }

    public function getAllForDataTable(?int $agendaId, string $tipo)
    {
        $query = DB::table('agenda_actividades')
            ->leftJoin('departamentos', 'agenda_actividades.departamento_id', '=', 'departamentos.id')
            ->select([
                'agenda_actividades.id',
                'agenda_actividades.actividad',
                'agenda_actividades.hora_desde_actividad',
                'agenda_actividades.hora_hasta_actividad',
                'agenda_actividades.fecha_del',
                'agenda_actividades.fecha_hasta',
                'departamentos.nombre_depa as equipo',
            ]);

        if (! $agendaId || ! in_array($tipo, ['D', 'S'], true)) {
            return $query->whereRaw('1 = 0');
        }

        $query
            ->where('agenda_actividades.agenda_id', $agendaId)
            ->where('agenda_actividades.tipo_actividad', $tipo);

        if ($tipo === 'D') {
            return $query
                ->orderBy('agenda_actividades.hora_desde_actividad')
                ->orderBy('agenda_actividades.id');
        }

        return $query
            ->orderBy('agenda_actividades.fecha_del')
            ->orderBy('agenda_actividades.fecha_hasta')
            ->orderBy('agenda_actividades.id');
    }

    public function autocomplete(string $term, int $limit = 10)
    {
        return DB::table('agenda_actividades')
            ->select('actividad')
            ->whereNotNull('actividad')
            ->where('actividad', 'like', '%' . trim($term) . '%')
            ->distinct()
            ->orderBy('actividad')
            ->limit($limit)
            ->pluck('actividad');
    }

    public function store(array $data, int $userId): int
    {
        try {
            return DB::transaction(function () use ($data, $userId) {
                $agenda = $this->findAgendaOrFail((int) $data['agenda_id']);
                [$fechaDel, $fechaHasta, $horaDesde, $horaHasta] = $this->resolveActivitySchedule($data, $agenda);

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

            throw new AgendaActividadException('Ocurrio un error al registrar la actividad.', 500);
        }
    }

    public function update(int $id, array $data): void
    {
        try {
            DB::transaction(function () use ($id, $data) {
                $actividad = $this->findActivityOrFail($id);
                $agenda = $this->findAgendaOrFail((int) $data['agenda_id']);
                [$fechaDel, $fechaHasta, $horaDesde, $horaHasta] = $this->resolveActivitySchedule($data, $agenda);

                DB::table('agenda_actividades')
                    ->where('id', $actividad->id)
                    ->update([
                        'fecha_del' => $fechaDel,
                        'fecha_hasta' => $fechaHasta,
                        'hora_desde_actividad' => $horaDesde,
                        'hora_hasta_actividad' => $horaHasta,
                        'actividad' => trim($data['actividad']),
                        'departamento_id' => $data['departamento_id'],
                        'tipo_actividad' => $data['tipo_actividad'],
                        'updated_at' => now(),
                    ]);
            });
        } catch (AgendaActividadException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            throw new AgendaActividadException('Ocurrio un error al actualizar la actividad.', 500);
        }
    }

    public function destroy(int $id): void
    {
        try {
            DB::transaction(function () use ($id) {
                $actividad = $this->findActivityOrFail($id);

                DB::table('agenda_actividades')
                    ->where('id', $actividad->id)
                    ->delete();
            });
        } catch (AgendaActividadException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            throw new AgendaActividadException('Ocurrio un error al eliminar la actividad.', 500);
        }
    }

    private function findAgendaOrFail(int $agendaId)
    {
        $agenda = DB::table('agendas')
            ->where('id', $agendaId)
            ->first();

        if (! $agenda) {
            throw new AgendaActividadException('La agenda no existe.', 404);
        }

        return $agenda;
    }

    private function findActivityOrFail(int $activityId)
    {
        $actividad = $this->findById($activityId);

        if (! $actividad) {
            throw new AgendaActividadException('La actividad no existe.', 404);
        }

        return $actividad;
    }

    private function resolveActivitySchedule(array $data, object $agenda): array
    {
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

        if ($data['tipo_actividad'] === 'S') {
            if ($fechaDel < $agenda->fecha_desde || $fechaHasta > $agenda->fecha_hasta) {
                throw new AgendaActividadException('Las fechas de la actividad deben estar dentro del periodo de la agenda principal.', 422);
            }
        }

        if ($data['tipo_actividad'] === 'D') {
            if ($horaDesde < $agenda->hora_desde || $horaHasta > $agenda->hora_hasta) {
                throw new AgendaActividadException('Las horas de la actividad deben estar dentro del horario de trabajo de la agenda principal.', 422);
            }
        }

        return [$fechaDel, $fechaHasta, $horaDesde, $horaHasta];
    }
}
