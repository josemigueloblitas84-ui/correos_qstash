<?php

namespace App\Services\Dashboard;

use App\Services\ConfiguracionSistemaService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ControlHorasService
{
    public function __construct(
        protected ConfiguracionSistemaService $configuracionSistemaService
    ) {
    }

    public function getPreviewDataForUser(int $userId): array
    {
        $configuracion = $this->configuracionSistemaService->getPresentationData();

        $usuario = User::query()
            ->select([
                'id',
                'name',
                'email',
                'cod_estudiante',
                'celular',
            ])
            ->find($userId);

        $registros = DB::table('registro_horas_validadas as rh')
            ->leftJoin('users as validador', 'validador.id', '=', 'rh.cod_usuario_validador')
            ->where('rh.cod_usuario', $userId)
            ->select([
                'rh.id',
                'rh.fecha',
                'rh.fecha_reg',
                'rh.hora_inicio',
                'rh.hora_fin',
                'rh.total_hora',
                'rh.cod_usuario_validador',
                'validador.name as validador_nombre',
                'validador.celular as validador_celular',
            ])
            ->orderBy('rh.fecha_reg')
            ->orderBy('rh.hora_inicio')
            ->get();

        $responsable = $registros
            ->filter(fn ($registro) => !empty($registro->cod_usuario_validador))
            ->sortByDesc('id')
            ->first();

        return [
            'estudiante' => [
                'nombre' => $usuario?->name ?? '',
                'codigo' => $usuario?->cod_estudiante ?? '',
                'telefono' => $usuario?->celular ?? '',
                'correo' => $usuario?->email ?? '',
            ],
            'responsable' => [
                'nombre' => $responsable?->validador_nombre ?? '',
                'celular' => $responsable?->validador_celular ?? '',
            ],
            'institucion' => [
                'nombre' => $configuracion['nombre_institucion'] ?? '',
                'telefono' => $configuracion['celular_institucional'] ?? '',
                'correo' => $configuracion['correo_institucional'] ?? '',
            ],
            'periodos' => $registros->map(function ($registro) {
                return [
                    'fecha' => $registro->fecha
                        ? Carbon::parse($registro->fecha)->format('d/m/Y')
                        : '',
                    'ingreso' => $this->formatShortTime($registro->hora_inicio),
                    'salida' => $this->formatShortTime($registro->hora_fin),
                    'carga_horaria' => $this->formatDecimalHoursShort($registro->total_hora),
                ];
            })->values()->all(),
        ];
    }

    public function getSummaryForUser(int $userId): array
    {
        $usuario = User::query()
            ->select([
                'id',
                'cantidad_horas_totales',
            ])
            ->find($userId);

        $horasTotales = (float) ($usuario?->cantidad_horas_totales ?? 0);

        $resumen = DB::table('registro_horas_validadas as rh')
            ->where('rh.cod_usuario', $userId)
            ->selectRaw('COALESCE(SUM(rh.total_hora), 0) as horas_validadas')
            ->selectRaw('MIN(DATE(rh.fecha_reg)) as fecha_desde')
            ->selectRaw('MAX(DATE(rh.fecha_reg)) as fecha_hasta')
            ->first();

        $horasValidadas = (float) ($resumen?->horas_validadas ?? 0);
        $horasPendientes = max($horasTotales - $horasValidadas, 0);

        return [
            'horas_totales' => $this->formatDecimalHours($horasTotales),
            'horas_validadas' => $this->formatDecimalHours($horasValidadas),
            'horas_pendientes' => $this->formatDecimalHours($horasPendientes),
            'fecha_desde' => $resumen?->fecha_desde
                ? Carbon::parse($resumen->fecha_desde)->format('d-m-Y')
                : null,
            'fecha_hasta' => $resumen?->fecha_hasta
                ? Carbon::parse($resumen->fecha_hasta)->format('d-m-Y')
                : null,
        ];
    }

    public function getRowsForUser(int $userId): Collection
    {
        $registros = DB::table('registro_horas_validadas as rh')
            ->where('rh.cod_usuario', $userId)
            ->select([
                'rh.id',
                'rh.fecha',
                'rh.fecha_reg',
                'rh.hora_inicio',
                'rh.hora_fin',
                'rh.total_hora',
            ])
            ->orderBy('rh.fecha_reg')
            ->orderBy('rh.hora_inicio')
            ->get();

        if ($registros->isEmpty()) {
            return collect();
        }

        $actividadesPorRegistro = $this->getActividadesPorRegistro(
            $registros->pluck('id')->all()
        );

        $fechasInforme = $registros
            ->pluck('fecha_reg')
            ->filter()
            ->map(fn ($fecha) => Carbon::parse($fecha)->toDateString())
            ->unique()
            ->values()
            ->all();

        $actividadesNoProgramadasPorFecha = $this->getActividadesNoProgramadasPorUsuarioYFecha(
            $userId,
            $fechasInforme
        );

        return $registros->map(function ($registro) use ($actividadesPorRegistro, $actividadesNoProgramadasPorFecha) {
            $fechaInforme = $registro->fecha_reg
                ? Carbon::parse($registro->fecha_reg)->toDateString()
                : null;

            $actividadesNormales = $actividadesPorRegistro->get($registro->id, collect());
            $actividadesNoProgramadas = $fechaInforme
                ? $actividadesNoProgramadasPorFecha->get($fechaInforme, collect())
                : collect();

            return [
                'id' => (int) $registro->id,
                'fecha' => Carbon::parse($registro->fecha)->format('d-m-Y'),
                'hora_inicio' => $this->formatTime($registro->hora_inicio),
                'hora_fin' => $this->formatTime($registro->hora_fin),
                'total_hora' => $this->formatDecimalHours($registro->total_hora),
                'actividades' => $actividadesNormales
                    ->concat($actividadesNoProgramadas)
                    ->values(),
                'horario' => 'Normal',
            ];
        });
    }

    private function getActividadesPorRegistro(array $registroIds): Collection
    {
        return DB::table('actividades_validadas as av')
            ->join('agenda_actividades as aa', 'aa.id', '=', 'av.actividad_id')
            ->whereIn('av.registro_horas_validada_id', $registroIds)
            ->select([
                'av.registro_horas_validada_id',
                'aa.id',
                'aa.agenda_id',
                'aa.actividad',
            ])
            ->orderBy('aa.agenda_id')
            ->orderBy('aa.id')
            ->get()
            ->groupBy('registro_horas_validada_id')
            ->map(function (Collection $actividades) {
                return $actividades->map(function ($actividad) {
                    return [
                        'id' => (int) $actividad->id,
                        'texto' => $this->formatActividadText($actividad),
                    ];
                });
            });
    }

    private function getActividadesNoProgramadasPorUsuarioYFecha(int $userId, array $fechas): Collection
    {
        if (empty($fechas)) {
            return collect();
        }

        return DB::table('agenda_actividad_informe as ai')
            ->where('ai.usuario_id', $userId)
            ->where('ai.tipo_actividad', 'P')
            ->whereIn(DB::raw('DATE(ai.fecha_actividad)'), $fechas)
            ->select([
                DB::raw('DATE(ai.fecha_actividad) as fecha_informe'),
                'ai.id',
                'ai.actividad',
            ])
            ->orderBy('fecha_informe')
            ->orderBy('ai.id')
            ->get()
            ->groupBy('fecha_informe')
            ->map(function (Collection $actividades) {
                return $actividades->map(function ($actividad) {
                    $texto = trim((string) $actividad->actividad);

                    return [
                        'id' => (int) $actividad->id,
                        'texto' => $texto !== '' ? $texto : 'Actividad no programada',
                    ];
                });
            });
    }

    private function formatActividadText(object $actividad): string
    {
        $texto = trim((string) $actividad->actividad);

        return $texto !== '' ? $texto : 'Sin actividad';
    }

    private function formatTime(?string $time): string
    {
        return $time ? substr($time, 0, 8) : '';
    }

    private function formatShortTime(?string $time): string
    {
        return $time ? substr($time, 0, 5) : '';
    }

    private function formatDecimalHours(null|float|string $hours): string
    {
        if ($hours === null || $hours === '') {
            return '00:00:00';
        }

        $minutes = (int) round(((float) $hours) * 60);
        $formattedHours = intdiv($minutes, 60);
        $formattedMinutes = $minutes % 60;

        return sprintf('%02d:%02d:00', $formattedHours, $formattedMinutes);
    }

    private function formatDecimalHoursShort(null|float|string $hours): string
    {
        if ($hours === null || $hours === '') {
            return '00:00';
        }

        $minutes = (int) round(((float) $hours) * 60);
        $formattedHours = intdiv($minutes, 60);
        $formattedMinutes = $minutes % 60;

        return sprintf('%02d:%02d', $formattedHours, $formattedMinutes);
    }
}
