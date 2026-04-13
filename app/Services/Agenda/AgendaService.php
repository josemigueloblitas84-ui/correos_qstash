<?php

namespace App\Services\Agenda;

use Illuminate\Support\Facades\DB;
use App\Services\Support\ActivityLogger;

class AgendaService
{
    public function store(array $data, int $userId): int
    {
        $horaDesde = $data['hora_inicio_hora'] . ':' . $data['hora_inicio_minuto'];
        $horaHasta = $data['hora_fin_hora'] . ':' . $data['hora_fin_minuto'];

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
    }

    public function getAllForDataTable()
    {
        return DB::table('agendas')
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
            ->orderByDesc('agendas.id');
    }

}
