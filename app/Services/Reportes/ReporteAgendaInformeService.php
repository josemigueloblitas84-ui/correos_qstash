<?php

namespace App\Services\Reportes;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReporteAgendaInformeService
{
    public function getDepartamentos()
    {
        return DB::table('departamentos')
            ->where('estado_depa', 'activo')
            ->orderBy('nombre_depa')
            ->get(['id', 'nombre_depa']);
    }

    public function getAgendaQuery(array $filters)
    {
        $query = DB::table('agendas as a')
            ->join('departamentos as d', 'd.id', '=', 'a.cod_unidad')
            ->join('users as u', 'u.id', '=', 'a.cod_solicitante')
            ->select([
                'a.id',
                'a.fecha',
                'a.fecha_desde',
                'a.fecha_hasta',
                'u.name as usuario_nombre',
                'd.nombre_depa as equipo_nombre',
            ]);

        if (!empty($filters['auth_user_id'])) {
            $authUserId = (int) $filters['auth_user_id'];

            $query->where(function ($subQuery) use ($authUserId) {
                $subQuery->where('a.cod_solicitante', $authUserId)
                    ->orWhereIn('a.cod_solicitante', function ($assignedQuery) use ($authUserId) {
                        $assignedQuery->select('cod_usuario_asignado')
                            ->from('agenda_personal_asignado')
                            ->where('cod_usuario', $authUserId);
                    });
            });
        }

        if (!empty($filters['fecha_desde'])) {
            $query->whereDate('a.fecha_desde', '>=', $filters['fecha_desde']);
        }

        if (!empty($filters['fecha_hasta'])) {
            $query->whereDate('a.fecha_desde', '<=', $filters['fecha_hasta']);
        }

        if (!empty($filters['equipo'])) {
            $query->where('a.cod_unidad', $filters['equipo']);
        }

        return $query->orderBy('a.fecha')
            ->orderBy('u.name')
            ->orderBy('d.nombre_depa')
            ->orderBy('a.fecha_desde')
            ->orderBy('a.fecha_hasta');
    }

    public function getInformeAgendaQuery(array $filters)
    {
        $query = DB::table('agenda_actividad_informe as ai')
            ->join('users as u', 'u.id', '=', 'ai.usuario_id')
            ->join('departamentos as d', 'd.id', '=', 'ai.departamento_id')
            ->select([
                DB::raw('DATE(ai.fecha_actividad) as fecha_actividad'),
                'ai.usuario_id',
                'u.name as usuario_nombre',
                DB::raw("GROUP_CONCAT(DISTINCT d.nombre_depa ORDER BY d.nombre_depa SEPARATOR ', ') as equipo_nombre"),
                DB::raw('MAX(ai.validada_encargado) as validada_encargado'),
            ])
            ->groupBy(
                DB::raw('DATE(ai.fecha_actividad)'),
                'ai.usuario_id',
                'u.name'
            );

        if (!empty($filters['auth_user_id'])) {
            $authUserId = (int) $filters['auth_user_id'];

            $query->where(function ($subQuery) use ($authUserId) {
                $subQuery->where('ai.usuario_id', $authUserId)
                    ->orWhereIn('ai.usuario_id', function ($assignedQuery) use ($authUserId) {
                        $assignedQuery->select('cod_usuario_asignado')
                            ->from('agenda_personal_asignado')
                            ->where('cod_usuario', $authUserId);
                    });
            });
        }

        if (!empty($filters['fecha_desde'])) {
            $query->whereDate('ai.fecha_actividad', '>=', $filters['fecha_desde']);
        }

        if (!empty($filters['fecha_hasta'])) {
            $query->whereDate('ai.fecha_actividad', '<=', $filters['fecha_hasta']);
        }

        if (!empty($filters['equipo'])) {
            $query->where('ai.departamento_id', $filters['equipo']);
        }

        return $query->orderBy('fecha_actividad')
            ->orderBy('u.name')
            ->orderBy('equipo_nombre');
    }

    public function getInformePreviewData(string $fechaActividad, int $usuarioId): ?array
    {
        $cabecera = DB::table('agenda_actividad_informe as ai')
            ->join('users as u', 'u.id', '=', 'ai.usuario_id')
            ->join('departamentos as d', 'd.id', '=', 'ai.departamento_id')
            ->select([
                DB::raw('DATE(ai.fecha_actividad) as fecha_actividad'),
                'u.name as usuario_nombre',
                DB::raw("GROUP_CONCAT(DISTINCT d.nombre_depa ORDER BY d.nombre_depa SEPARATOR ', ') as equipo_nombre"),
            ])
            ->whereDate('ai.fecha_actividad', $fechaActividad)
            ->where('ai.usuario_id', $usuarioId)
            ->groupBy(DB::raw('DATE(ai.fecha_actividad)'), 'u.name')
            ->first();

        if (!$cabecera) {
            return null;
        }

        $actividades = DB::table('agenda_actividad_informe as ai')
            ->join('departamentos as d', 'd.id', '=', 'ai.departamento_id')
            ->select([
                'ai.id',
                'ai.actividad',
                'ai.tipo_actividad',
                'ai.estado',
                'ai.detalle_estado',
                'd.nombre_depa as equipo_nombre',
            ])
            ->whereDate('ai.fecha_actividad', $fechaActividad)
            ->where('ai.usuario_id', $usuarioId)
            ->orderByRaw("FIELD(ai.tipo_actividad, 'D', 'S', 'P')")
            ->orderBy('ai.id')
            ->get();

        return [
            'informe' => $cabecera,
            'actividadesDiarias' => $actividades->where('tipo_actividad', 'D')->values(),
            'actividadesSemanales' => $actividades->where('tipo_actividad', 'S')->values(),
            'actividadesNoProgramadas' => $actividades->where('tipo_actividad', 'P')->values(),
        ];
    }

    public function validateInforme(string $fechaActividad, int $usuarioId, int $validadorId): void
    {
        DB::transaction(function () use ($fechaActividad, $usuarioId, $validadorId) {
            $agenda = DB::table('agenda_actividad_informe as ai')
                ->join('agendas as a', 'a.id', '=', 'ai.agenda_id')
                ->select([
                    'a.id as agenda_id',
                    'a.cod_solicitante',
                    'a.hora_desde',
                    'a.hora_hasta',
                ])
                ->whereDate('ai.fecha_actividad', $fechaActividad)
                ->where('ai.usuario_id', $usuarioId)
                ->orderBy('ai.id')
                ->first();

            if (!$agenda) {
                throw new \RuntimeException('No se encontró el informe a validar.');
            }

            $horaInicio = Carbon::createFromFormat('H:i', substr($agenda->hora_desde, 0, 5));
            $horaFin = Carbon::createFromFormat('H:i', substr($agenda->hora_hasta, 0, 5));
            $totalHoras = $horaInicio->diffInMinutes($horaFin) / 60;
            $fechaClickValidacion = now()->toDateString();
            $fechaInformeTimestamp = $fechaActividad . ' 00:00:00';

            $registroHora = DB::table('registro_horas_validadas')
                ->where('cod_usuario', $agenda->cod_solicitante)
                ->where('fecha_reg', $fechaInformeTimestamp)
                ->first();

            if ($registroHora) {
                DB::table('registro_horas_validadas')
                    ->where('id', $registroHora->id)
                    ->update([
                        'cod_usuario_validador' => $validadorId,
                        'fecha' => $fechaClickValidacion,
                        'hora_inicio' => $horaInicio->format('H:i:s'),
                        'hora_fin' => $horaFin->format('H:i:s'),
                        'total_hora' => $totalHoras,
                        'actividad_masivo' => '',
                        'actividad_pasivo' => '',
                        'fecha_reg' => $fechaInformeTimestamp,
                    ]);

                $registroHoraId = (int) $registroHora->id;
            } else {
                $registroHoraId = DB::table('registro_horas_validadas')->insertGetId([
                    'cod_usuario' => $agenda->cod_solicitante,
                    'cod_usuario_validador' => $validadorId,
                    'fecha' => $fechaClickValidacion,
                    'hora_inicio' => $horaInicio->format('H:i:s'),
                    'hora_fin' => $horaFin->format('H:i:s'),
                    'total_hora' => $totalHoras,
                    'actividad_masivo' => '',
                    'actividad_pasivo' => '',
                    'fecha_reg' => $fechaInformeTimestamp,
                ]);
            }

            $actividadIds = DB::table('agenda_actividad_informe')
                ->whereDate('fecha_actividad', $fechaActividad)
                ->where('usuario_id', $usuarioId)
                ->whereNotNull('agenda_actividad_id')
                ->pluck('agenda_actividad_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            DB::table('actividades_validadas')
                ->where('registro_horas_validada_id', $registroHoraId)
                ->delete();

            if (!empty($actividadIds)) {
                $rows = [];

                foreach ($actividadIds as $actividadId) {
                    $rows[] = [
                        'registro_horas_validada_id' => $registroHoraId,
                        'actividad_id' => $actividadId,
                    ];
                }

                DB::table('actividades_validadas')->insert($rows);
            }

            DB::table('agenda_actividad_informe')
                ->whereDate('fecha_actividad', $fechaActividad)
                ->where('usuario_id', $usuarioId)
                ->update([
                    'validada_encargado' => 1,
                    'usuario_actualizador_id' => $validadorId,
                    'updated_at' => now(),
                ]);
        });
    }
}
