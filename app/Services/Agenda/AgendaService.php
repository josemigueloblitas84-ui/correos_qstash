<?php

namespace App\Services\Agenda;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Services\Support\ActivityLogger;

class AgendaService
{
    public function findById(int $id)
    {
        return DB::table('agendas')
            ->where('id', $id)
            ->first();
    }

    public function findAccessibleById(int $id, $user)
    {
        $query = DB::table('agendas')
            ->where('id', $id);

        if (! $user->hasRole('SuperAdministrador')) {
            $query->where(function ($subQuery) use ($user) {
                $subQuery->where('agendas.cod_solicitante', $user->id)
                    ->orWhereIn('agendas.cod_solicitante', function ($assignedQuery) use ($user) {
                        $assignedQuery->select('cod_usuario_asignado')
                            ->from('agenda_personal_asignado')
                            ->where('cod_usuario', $user->id);
                    });
            });
        }

        return $query->first();
    }

    public function store(array $data, int $userId): int
    {
        $horaDesde = $data['hora_inicio_hora'] . ':' . $data['hora_inicio_minuto'];
        $horaHasta = $data['hora_fin_hora'] . ':' . $data['hora_fin_minuto'];

        return DB::transaction(function () use ($data, $userId, $horaDesde, $horaHasta) {
            $hasConflict = DB::table('agendas')
                ->where('cod_unidad', $data['cod_unidad'])
                ->where('cod_solicitante', $data['cod_solicitante'])
                ->where(function ($query) use ($data) {
                    $query->where('fecha_desde', '<=', $data['fecha_hasta'])
                        ->where('fecha_hasta', '>=', $data['fecha_desde']);
                })
                ->exists();

            if ($hasConflict) {
                throw ValidationException::withMessages([
                    'fecha_desde' => 'Ya existe una agenda registrada para este solicitante y departamento dentro de ese período.',
                ]);
            }

            $agendaId = DB::table('agendas')->insertGetId([
                'fecha' => $data['fecha'],
                'cod_unidad' => $data['cod_unidad'],
                'cod_solicitante' => $data['cod_solicitante'],
                'fecha_desde' => $data['fecha_desde'],
                'fecha_hasta' => $data['fecha_hasta'],
                'hora_desde' => $horaDesde,
                'hora_hasta' => $horaHasta,
                'cod_usuario' => $userId,
                'cerrado' => 0,
                'estado_agenda' => 'A',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            ActivityLogger::log(
                'Agenda creada',
                [
                    'agenda' => [
                        'id' => $agendaId,
                        'fecha' => $data['fecha'],
                        'cod_unidad' => $data['cod_unidad'],
                        'cod_solicitante' => $data['cod_solicitante'],
                        'fecha_desde' => $data['fecha_desde'],
                        'fecha_hasta' => $data['fecha_hasta'],
                        'hora_desde' => $horaDesde,
                        'hora_hasta' => $horaHasta,
                        'cod_usuario' => $userId,
                        'cerrado' => 0,
                        'estado_agenda' => 'A',
                    ],
                ],
                logName: 'agendas',
                event: 'created'
            );

            return $agendaId;
        });
    }

    public function getAllForDataTable($user)
    {
        $query = DB::table('agendas')
            ->join('departamentos', 'agendas.cod_unidad', '=', 'departamentos.id')
            ->join('users', 'agendas.cod_solicitante', '=', 'users.id')
            ->select([
                'agendas.id',
                'agendas.fecha',
                'agendas.fecha_desde',
                'agendas.fecha_hasta',
                'agendas.hora_desde',
                'agendas.hora_hasta',
                'departamentos.nombre_depa as departamento',
                'users.name as nombre_apellido',
            ])
            ->where('agendas.estado_agenda', 'A');

        if (! $user->hasRole('SuperAdministrador')) {
            $query->where(function ($subQuery) use ($user) {
                $subQuery->where('agendas.cod_solicitante', $user->id)
                    ->orWhereIn('agendas.cod_solicitante', function ($assignedQuery) use ($user) {
                        $assignedQuery->select('cod_usuario_asignado')
                            ->from('agenda_personal_asignado')
                            ->where('cod_usuario', $user->id);
                    });
            });
        }

        return $query->orderByDesc('agendas.id');
    }

    public function getPreviewData(int $id): ?array
    {
        $agenda = DB::table('agendas')
            ->join('departamentos', 'agendas.cod_unidad', '=', 'departamentos.id')
            ->join('users', 'agendas.cod_solicitante', '=', 'users.id')
            ->select([
                'agendas.id',
                'agendas.fecha',
                'agendas.fecha_desde',
                'agendas.fecha_hasta',
                'agendas.hora_desde',
                'agendas.hora_hasta',
                'departamentos.nombre_depa as departamento',
                'users.name as solicitante',
            ])
            ->where('agendas.id', $id)
            ->first();

        if (! $agenda) {
            return null;
        }

        $actividadesDiarias = DB::table('agenda_actividades')
            ->leftJoin('departamentos', 'agenda_actividades.departamento_id', '=', 'departamentos.id')
            ->select([
                'agenda_actividades.id',
                'agenda_actividades.actividad',
                'agenda_actividades.hora_desde_actividad',
                'agenda_actividades.hora_hasta_actividad',
                'departamentos.nombre_depa as equipo',
            ])
            ->where('agenda_actividades.agenda_id', $id)
            ->where('agenda_actividades.tipo_actividad', 'D')
            ->orderBy('agenda_actividades.hora_desde_actividad')
            ->orderBy('agenda_actividades.id')
            ->get();

        $actividadesSemanales = DB::table('agenda_actividades')
            ->leftJoin('departamentos', 'agenda_actividades.departamento_id', '=', 'departamentos.id')
            ->select([
                'agenda_actividades.id',
                'agenda_actividades.actividad',
                'agenda_actividades.fecha_del',
                'agenda_actividades.fecha_hasta',
                'departamentos.nombre_depa as equipo',
            ])
            ->where('agenda_actividades.agenda_id', $id)
            ->where('agenda_actividades.tipo_actividad', 'S')
            ->orderBy('agenda_actividades.fecha_del')
            ->orderBy('agenda_actividades.fecha_hasta')
            ->orderBy('agenda_actividades.id')
            ->get();

        return [
            'agenda' => $agenda,
            'dailyActivities' => $actividadesDiarias,
            'weeklyActivities' => $actividadesSemanales,
        ];
    }

    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            $agenda = DB::table('agendas')
                ->where('id', $id)
                ->first();

            if (! $agenda) {
                return;
            }

            DB::table('agendas')
                ->where('id', $agenda->id)
                ->delete();

            ActivityLogger::log(
                'Agenda eliminada',
                [
                    'agenda' => (array) $agenda,
                ],
                null,
                logName: 'agendas',
                event: 'deleted'
            );
        });
    }

    public function send(int $id): void
    {
        DB::transaction(function () use ($id) {
            $agenda = DB::table('agendas')
                ->where('id', $id)
                ->first();

            if (! $agenda) {
                return;
            }

            DB::table('agendas')
                ->where('id', $agenda->id)
                ->update([
                    'estado_agenda' => 'C',
                    'updated_at' => now(),
                ]);

            ActivityLogger::log(
                'Agenda enviada',
                [
                    'agenda' => array_merge((array) $agenda, [
                        'estado_agenda_nuevo' => 'C',
                    ]),
                ],
                null,
                logName: 'agendas',
                event: 'updated'
            );
        });
    }
}
