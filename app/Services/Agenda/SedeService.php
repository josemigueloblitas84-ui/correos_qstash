<?php

namespace App\Services\Agenda;

use App\Services\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;

class SedeService
{
    public function getAllForDataTable()
    {
        return DB::table('sedes')
            ->select('id', 'nombre', 'created_at')
            ->orderBy('id', 'desc');
    }

    public function store(array $data): int
    {
        return DB::transaction(function () use ($data) {
            $sedeId = DB::table('sedes')->insertGetId([
                'nombre' => $data['nombre'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            ActivityLogger::log(
                'Sede creada',
                [
                    'attributes' => [
                        'id' => $sedeId,
                        'nombre' => $data['nombre'],
                    ],
                ],
                null,
                null,
                'sedes',
                'created'
            );

            return $sedeId;
        });
    }

    public function findById(int $id)
    {
        return DB::table('sedes')
            ->where('id', $id)
            ->first();
    }

    public function update(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $sede = $this->findById($id);

            $updated = DB::table('sedes')
                ->where('id', $id)
                ->update([
                    'nombre' => $data['nombre'],
                    'updated_at' => now(),
                ]) > 0;

            if ($updated && $sede) {
                ActivityLogger::log(
                    'Sede actualizada',
                    [
                        'old' => [
                            'id' => $sede->id,
                            'nombre' => $sede->nombre,
                        ],
                        'attributes' => [
                            'id' => $id,
                            'nombre' => $data['nombre'],
                        ],
                    ],
                    null,
                    null,
                    'sedes',
                    'updated'
                );
            }

            return $updated;
        });
    }

    public function destroy(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $sede = $this->findById($id);

            $deleted = DB::table('sedes')
                ->where('id', $id)
                ->delete() > 0;

            if ($deleted && $sede) {
                ActivityLogger::log(
                    'Sede eliminada',
                    [
                        'old' => [
                            'id' => $sede->id,
                            'nombre' => $sede->nombre,
                        ],
                    ],
                    null,
                    null,
                    'sedes',
                    'deleted'
                );
            }

            return $deleted;
        });
    }
}
