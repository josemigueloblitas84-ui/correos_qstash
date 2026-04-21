<?php

namespace App\Services\Agenda;

use Illuminate\Support\Facades\DB;
use App\Services\Support\ActivityLogger;


class DepartamentoService
{
    public function getAllForDataTable()
    {
        return DB::table('departamentos')
            ->select('id', 'nombre_depa', 'estado_depa', 'created_at')
            ->orderBy('id', 'desc');
    }

    public function store(array $data): int
    {
        $departamentoId = DB::table('departamentos')->insertGetId([
            'nombre_depa' => $data['nombre_depa'],
            'estado_depa' => $data['estado_depa'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ActivityLogger::log(
            'Departamento creado',
            [
                'attributes' => [
                    'id' => $departamentoId,
                    'nombre_depa' => $data['nombre_depa'],
                    'estado_depa' => $data['estado_depa'],
                ],
            ],
            null,
            null,
            'departamentos',
            'created'
        );

        return $departamentoId;
    }

    public function findById(int $id)
    {
        return DB::table('departamentos')
            ->where('id', $id)
            ->first();
    }

    public function update(int $id, array $data): bool
    {
        $departamento = $this->findById($id);

        $updated = DB::table('departamentos')
            ->where('id', $id)
            ->update([
                'nombre_depa' => $data['nombre_depa'],
                'estado_depa' => $data['estado_depa'],
                'updated_at' => now(),
            ]) > 0;

        if ($updated && $departamento) {
            ActivityLogger::log(
                'Departamento actualizado',
                [
                    'old' => [
                        'id' => $departamento->id,
                        'nombre_depa' => $departamento->nombre_depa,
                        'estado_depa' => $departamento->estado_depa,
                    ],
                    'attributes' => [
                        'id' => $id,
                        'nombre_depa' => $data['nombre_depa'],
                        'estado_depa' => $data['estado_depa'],
                    ],
                ],
                null,
                null,
                'departamentos',
                'updated'
            );
        }
        return $updated;
    }

    public function deactivate(int $id): bool
    {
        $departamento = $this->findById($id);

        $updated = DB::table('departamentos')
            ->where('id', $id)
            ->update([
                'estado_depa' => 'inactivo',
                'updated_at' => now(),
            ]) > 0;

        if ($updated && $departamento) {
            ActivityLogger::log(
                'Departamento desactivado',
                [
                    'old' => [
                        'id' => $departamento->id,
                        'nombre_depa' => $departamento->nombre_depa,
                        'estado_depa' => $departamento->estado_depa,
                    ],
                    'attributes' => [
                        'id' => $departamento->id,
                        'nombre_depa' => $departamento->nombre_depa,
                        'estado_depa' => 'inactivo',
                    ],
                ],
                null,
                null,
                'departamentos',
                'status_updated'
            );
        }
        return $updated;
    }

    public function toggleStatus(int $id, string $estadoActual): string
    {
        $departamento = $this->findById($id);
        $nuevoEstado = strtolower($estadoActual) === 'activo' ? 'inactivo' : 'activo';

        DB::table('departamentos')
            ->where('id', $id)
            ->update([
                'estado_depa' => $nuevoEstado,
                'updated_at' => now(),
            ]);
            if ($departamento) {
                ActivityLogger::log(
                    'Estado de departamento actualizado',
                    [
                        'old' => [
                            'id' => $departamento->id,
                            'nombre_depa' => $departamento->nombre_depa,
                            'estado_depa' => $departamento->estado_depa,
                        ],
                        'attributes' => [
                            'id' => $departamento->id,
                            'nombre_depa' => $departamento->nombre_depa,
                            'estado_depa' => $nuevoEstado,
                        ],
                    ],
                    null,
                    null,
                    'departamentos',
                    'status_updated'
                );
            }

        return $nuevoEstado;
    }

    public function getActiveForSelect()
    {
        return DB::table('departamentos')
            ->select('id', 'nombre_depa')
            ->where('estado_depa', 'activo')
            ->orderBy('nombre_depa', 'asc')
            ->get();
    }
}
