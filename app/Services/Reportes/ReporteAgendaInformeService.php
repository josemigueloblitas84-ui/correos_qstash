<?php
/*
namespace App\Services\Reportes;

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
                'd.nombre_depa as equipo_nombre',
                DB::raw('MAX(ai.validada_encargado) as validada_encargado'),
            ])
            ->groupBy(
                DB::raw('DATE(ai.fecha_actividad)'),
                'ai.usuario_id',
                'u.name',
                'd.nombre_depa'
            );

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
            ->orderBy('d.nombre_depa');
    }

}*/
namespace App\Services\Reportes;

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
                'ai.departamento_id',
                'u.name as usuario_nombre',
                'd.nombre_depa as equipo_nombre',
                DB::raw('MAX(ai.validada_encargado) as validada_encargado'),
            ])
            ->groupBy(
                DB::raw('DATE(ai.fecha_actividad)'),
                'ai.usuario_id',
                'ai.departamento_id',
                'u.name',
                'd.nombre_depa'
            );

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
            ->orderBy('d.nombre_depa');
    }

    public function getInformePreviewData(string $fechaActividad, int $usuarioId, int $departamentoId): ?array
    {
        $cabecera = DB::table('agenda_actividad_informe as ai')
            ->join('users as u', 'u.id', '=', 'ai.usuario_id')
            ->join('departamentos as d', 'd.id', '=', 'ai.departamento_id')
            ->select([
                'ai.fecha_actividad',
                'u.name as usuario_nombre',
                'd.nombre_depa as equipo_nombre',
            ])
            ->whereDate('ai.fecha_actividad', $fechaActividad)
            ->where('ai.usuario_id', $usuarioId)
            ->where('ai.departamento_id', $departamentoId)
            ->first();

        if (! $cabecera) {
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
            ->where('ai.departamento_id', $departamentoId)
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
}
