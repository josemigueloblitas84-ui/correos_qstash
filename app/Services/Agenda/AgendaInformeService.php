<?php

namespace App\Services\Agenda;

use App\Exceptions\AgendaInformeException;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class AgendaInformeService
{
    public function getFormularioData(int $userId, Carbon $fecha): array
    {
        $fechaBase = $fecha->toDateString();

        $actividades = DB::table('agenda_actividades as aa')
            ->join('agendas as a', 'a.id', '=', 'aa.agenda_id')
            ->leftJoin('departamentos as d', 'd.id', '=', 'aa.departamento_id')
            ->leftJoin('agenda_actividad_informe as ai', function ($join) use ($fechaBase, $userId) {
                $join->on('ai.agenda_actividad_id', '=', 'aa.id')
                    ->whereDate('ai.fecha_actividad', '=', $fechaBase)
                    ->where('ai.usuario_id', '=', $userId);
            })
            ->where('aa.usuario_id', $userId)
            ->whereDate('a.fecha_desde', '<=', $fechaBase)
            ->whereDate('a.fecha_hasta', '>=', $fechaBase)
            ->whereIn('aa.tipo_actividad', ['D', 'S'])
            ->select([
                'aa.id as agenda_actividad_id',
                'aa.agenda_id',
                'aa.actividad',
                'aa.departamento_id',
                'aa.tipo_actividad',
                'aa.fecha_del',
                'aa.fecha_hasta',
                'd.nombre_depa as equipo',
                'ai.estado as informe_estado',
                'ai.detalle_estado as informe_detalle',
            ])
            ->orderBy('aa.id')
            ->get();

        $actividadesDiarias = $actividades
            ->where('tipo_actividad', 'D')
            ->values();

        $actividadesSemanales = $actividades
            ->filter(function ($actividad) use ($fechaBase) {
                if ($actividad->tipo_actividad !== 'S') {
                    return false;
                }

                if ($actividad->fecha_del && $actividad->fecha_del > $fechaBase) {
                    return false;
                }

                if ($actividad->fecha_hasta && $actividad->fecha_hasta < $fechaBase) {
                    return false;
                }

                return true;
            })
            ->values();

        $agendaIdActual = $actividades->first()->agenda_id
            ?? DB::table('agendas')
                ->where('cod_usuario', $userId)
                ->whereDate('fecha_desde', '<=', $fechaBase)
                ->whereDate('fecha_hasta', '>=', $fechaBase)
                ->orderByDesc('id')
                ->value('id');

        $actividadesNoProgramadas = DB::table('agenda_actividad_informe as ai')
            ->leftJoin('departamentos as d', 'd.id', '=', 'ai.departamento_id')
            ->where('ai.usuario_id', $userId)
            ->whereDate('ai.fecha_actividad', $fechaBase)
            ->where('ai.tipo_actividad', 'P')
            ->select([
                'ai.id',
                'ai.agenda_id',
                'ai.actividad',
                'ai.departamento_id',
                'd.nombre_depa as equipo',
            ])
            ->orderBy('ai.id')
            ->get();

        $departamentos = DB::table('departamentos')
            ->where('estado_depa', 'activo')
            ->orderBy('nombre_depa')
            ->get(['id', 'nombre_depa']);

        return [
            'actividadesDiarias' => $actividadesDiarias,
            'actividadesSemanales' => $actividadesSemanales,
            'actividadesNoProgramadas' => $actividadesNoProgramadas,
            'departamentos' => $departamentos,
            'agendaIdActual' => $agendaIdActual,
        ];
    }

    public function store(array $data, int $userId): void
    {
        try {
            DB::transaction(function () use ($data, $userId) {
                $fecha = $data['fecha'];
                $hoy = now()->toDateString();
                $ahora = now();

                foreach ($data['programadas'] ?? [] as $item) {
                    DB::table('agenda_actividad_informe')->updateOrInsert(
                        [
                            'agenda_actividad_id' => $item['agenda_actividad_id'],
                            'fecha_actividad' => $fecha,
                            'tipo_actividad' => $item['tipo_actividad'],
                        ],
                        [
                            'agenda_id' => $item['agenda_id'],
                            'actividad' => trim($item['actividad']),
                            'departamento_id' => $item['departamento_id'],
                            'usuario_id' => $userId,
                            'estado' => !empty($item['estado']) ? 1 : 0,
                            'detalle_estado' => filled($item['detalle_estado'] ?? null)
                                ? trim($item['detalle_estado'])
                                : null,
                            'reprogramado' => null,
                            'fecha_actualizacion' => $hoy,
                            'validada_encargado' => 0,
                            'usuario_actualizador_id' => $userId,
                            'updated_at' => $ahora,
                            'created_at' => $ahora,
                        ]
                    );
                }

                DB::table('agenda_actividad_informe')
                    ->where('usuario_id', $userId)
                    ->whereDate('fecha_actividad', $fecha)
                    ->where('tipo_actividad', 'P')
                    ->delete();

                $noProgramadas = [];

                foreach ($data['no_programadas'] ?? [] as $item) {
                    $actividad = trim($item['actividad'] ?? '');

                    if ($actividad === '') {
                        continue;
                    }

                    $noProgramadas[] = [
                        'agenda_actividad_id' => null,
                        'agenda_id' => $item['agenda_id'],
                        'fecha_actividad' => $fecha,
                        'actividad' => $actividad,
                        'departamento_id' => $item['departamento_id'],
                        'tipo_actividad' => 'P',
                        'usuario_id' => $userId,
                        'estado' => 1,
                        'detalle_estado' => null,
                        'reprogramado' => null,
                        'fecha_actualizacion' => $hoy,
                        'validada_encargado' => 0,
                        'usuario_actualizador_id' => $userId,
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ];
                }

                if (!empty($noProgramadas)) {
                    DB::table('agenda_actividad_informe')->insert($noProgramadas);
                }
            });
        } catch (AgendaInformeException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            throw new AgendaInformeException('No se pudo guardar el informe.', 500);
        }
    }

    public function storeNoProgramada(array $data, int $userId): array
    {
        try {
            $departamento = DB::table('departamentos')
                ->where('id', $data['departamento_id'])
                ->first();

            $id = DB::table('agenda_actividad_informe')->insertGetId([
                'agenda_actividad_id' => null,
                'agenda_id' => $data['agenda_id'],
                'fecha_actividad' => $data['fecha'],
                'actividad' => trim($data['actividad']),
                'departamento_id' => $data['departamento_id'],
                'tipo_actividad' => 'P',
                'usuario_id' => $userId,
                'estado' => 1,
                'detalle_estado' => 'Realizado',
                'reprogramado' => null,
                'fecha_actualizacion' => now()->toDateString(),
                'validada_encargado' => 0,
                'usuario_actualizador_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'id' => $id,
                'agenda_id' => (int) $data['agenda_id'],
                'actividad' => trim($data['actividad']),
                'departamento_id' => (int) $data['departamento_id'],
                'equipo' => $departamento?->nombre_depa,
            ];
        } catch (AgendaInformeException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            throw new AgendaInformeException('No se pudo registrar la actividad no programada.', 500);
        }
    }

}
