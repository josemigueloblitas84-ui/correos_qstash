<?php

namespace App\Services\Agenda;

use App\Services\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InstitucionService
{
    public function getForSelect()
    {
        return DB::table('instituciones')
            ->select('id', 'nombre')
            ->orderBy('nombre', 'asc')
            ->get();
    }

    public function getAllForDataTable()
    {
        return DB::table('instituciones')
            ->leftJoin('institucion_sede', 'instituciones.id', '=', 'institucion_sede.institucion_id')
            ->leftJoin('sedes', 'institucion_sede.sede_id', '=', 'sedes.id')
            ->select(
                'instituciones.id',
                'instituciones.nombre',
                'instituciones.created_at',
                DB::raw('COUNT(sedes.id) as sedes_count'),
                DB::raw("GROUP_CONCAT(sedes.nombre ORDER BY sedes.nombre SEPARATOR '||') as sedes_nombres")
            )
            ->groupBy('instituciones.id', 'instituciones.nombre', 'instituciones.created_at')
            ->orderBy('instituciones.id', 'desc');
    }

    public function store(array $data): int
    {
        return DB::transaction(function () use ($data) {
            $institucionId = DB::table('instituciones')->insertGetId([
                'nombre' => $data['nombre'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            ActivityLogger::log(
                'Institucion creada',
                [
                    'attributes' => [
                        'id' => $institucionId,
                        'nombre' => $data['nombre'],
                    ],
                ],
                null,
                null,
                'instituciones',
                'created'
            );

            return $institucionId;
        });
    }

    public function findById(int $id)
    {
        return DB::table('instituciones')
            ->where('id', $id)
            ->first();
    }

    public function getSedesForSelectByInstitucion(int $institucionId)
    {
        return DB::table('institucion_sede')
            ->join('sedes', 'institucion_sede.sede_id', '=', 'sedes.id')
            ->where('institucion_sede.institucion_id', $institucionId)
            ->select('sedes.id', 'sedes.nombre')
            ->distinct()
            ->orderBy('sedes.nombre', 'asc')
            ->get()
            ->map(function ($sede) {
                return [
                    'id' => (int) $sede->id,
                    'nombre' => $sede->nombre,
                ];
            })
            ->values()
            ->all();
    }

    public function update(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $institucion = $this->findById($id);

            $updated = DB::table('instituciones')
                ->where('id', $id)
                ->update([
                    'nombre' => $data['nombre'],
                    'updated_at' => now(),
                ]) > 0;

            if ($updated && $institucion) {
                ActivityLogger::log(
                    'Institucion actualizada',
                    [
                        'old' => [
                            'id' => $institucion->id,
                            'nombre' => $institucion->nombre,
                        ],
                        'attributes' => [
                            'id' => $id,
                            'nombre' => $data['nombre'],
                        ],
                    ],
                    null,
                    null,
                    'instituciones',
                    'updated'
                );
            }

            return $updated;
        });
    }

    public function destroy(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $institucion = $this->findById($id);

            $deleted = DB::table('instituciones')
                ->where('id', $id)
                ->delete() > 0;

            if ($deleted && $institucion) {
                ActivityLogger::log(
                    'Institucion eliminada',
                    [
                        'old' => [
                            'id' => $institucion->id,
                            'nombre' => $institucion->nombre,
                        ],
                    ],
                    null,
                    null,
                    'instituciones',
                    'deleted'
                );
            }

            return $deleted;
        });
    }

    public function getSedeAssignmentData(int $id): array
    {
        $institucion = $this->findById($id);

        if (! $institucion) {
            return [
                'institucion' => null,
                'sedes' => [],
            ];
        }

        $assignedSedeIds = DB::table('institucion_sede')
            ->where('institucion_id', $id)
            ->pluck('sede_id')
            ->map(fn ($sedeId) => (int) $sedeId)
            ->all();

        $sedes = DB::table('sedes')
            ->select('id', 'nombre')
            ->orderBy('nombre', 'asc')
            ->get()
            ->map(function ($sede) use ($assignedSedeIds) {
                return [
                    'id' => (int) $sede->id,
                    'nombre' => $sede->nombre,
                    'checked' => in_array((int) $sede->id, $assignedSedeIds, true),
                ];
            })
            ->values()
            ->all();

        return [
            'institucion' => $institucion,
            'sedes' => $sedes,
        ];
    }

    public function syncSedes(int $id, array $sedeIds = []): void
    {
        DB::transaction(function () use ($id, $sedeIds) {
            $institucion = $this->findById($id);
            $currentSedeIds = DB::table('institucion_sede')
                ->where('institucion_id', $id)
                ->pluck('sede_id')
                ->map(fn ($sedeId) => (int) $sedeId)
                ->all();

            $normalizedSedeIds = array_values(array_unique(array_map('intval', $sedeIds)));
            $sedeIdsToInsert = array_values(array_diff($normalizedSedeIds, $currentSedeIds));
            $sedeIdsToDelete = array_values(array_diff($currentSedeIds, $normalizedSedeIds));

            if ($sedeIdsToDelete !== []) {
                $usedSedeIds = DB::table('users')
                    ->where('institucion_id', $id)
                    ->whereIn('sede_id', $sedeIdsToDelete)
                    ->pluck('sede_id')
                    ->map(fn ($sedeId) => (int) $sedeId)
                    ->unique()
                    ->values()
                    ->all();

                if ($usedSedeIds !== []) {
                    $usedSedeNames = DB::table('sedes')
                        ->whereIn('id', $usedSedeIds)
                        ->orderBy('nombre', 'asc')
                        ->pluck('nombre')
                        ->all();

                    throw ValidationException::withMessages([
                        'sedes' => 'No puedes quitar sedes que ya estan asignadas a usuarios: ' . implode(', ', $usedSedeNames) . '.',
                    ]);
                }
            }

            if ($sedeIdsToDelete !== []) {
                DB::table('institucion_sede')
                    ->where('institucion_id', $id)
                    ->whereIn('sede_id', $sedeIdsToDelete)
                    ->delete();
            }

            if ($sedeIdsToInsert !== []) {
                $now = now();
                $rows = [];

                foreach ($sedeIdsToInsert as $sedeId) {
                    $rows[] = [
                        'institucion_id' => $id,
                        'sede_id' => $sedeId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                DB::table('institucion_sede')->insert($rows);
            }

            if ($institucion) {
                ActivityLogger::log(
                    'Sedes de institucion actualizadas',
                    [
                        'old' => [
                            'id' => $institucion->id,
                            'nombre' => $institucion->nombre,
                            'sedes' => $currentSedeIds,
                        ],
                        'attributes' => [
                            'id' => $institucion->id,
                            'nombre' => $institucion->nombre,
                            'sedes' => $normalizedSedeIds,
                        ],
                    ],
                    null,
                    null,
                    'instituciones',
                    'sedes_updated'
                );
            }
        });
    }
}
