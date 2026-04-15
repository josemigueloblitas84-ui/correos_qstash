<?php

namespace App\Services\Dashboard;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ControlHorasService
{
    public function getRowsForUser(int $userId): Collection
    {
        $registros = DB::table('registro_horas_validadas as rh')
            ->where('rh.cod_usuario', $userId)
            ->select([
                'rh.id',
                'rh.fecha',
                'rh.hora_inicio',
                'rh.hora_fin',
                'rh.total_hora',
            ])
            ->orderBy('rh.fecha')
            ->orderBy('rh.hora_inicio')
            ->get();

        if ($registros->isEmpty()) {
            return collect();
        }

        $actividadesPorRegistro = $this->getActividadesPorRegistro(
            $registros->pluck('id')->all()
        );

        return $registros->map(function ($registro) use ($actividadesPorRegistro) {
            return [
                'id' => (int) $registro->id,
                'fecha' => Carbon::parse($registro->fecha)->format('d-m-Y'),
                'hora_inicio' => $this->formatTime($registro->hora_inicio),
                'hora_fin' => $this->formatTime($registro->hora_fin),
                'total_hora' => $this->formatDecimalHours($registro->total_hora),
                'actividades' => $actividadesPorRegistro->get($registro->id, collect())->values(),
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

    private function formatActividadText(object $actividad): string
    {
        $texto = trim((string) $actividad->actividad);

        return trim($actividad->agenda_id . ' ' . $actividad->id . ' ' . $texto);
    }

    private function formatTime(?string $time): string
    {
        return $time ? substr($time, 0, 8) : '';
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
}
