<?php

namespace App\Services\Agenda;

use Illuminate\Support\Facades\DB;

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
        return DB::table('departamentos')->insertGetId([
            'nombre_depa' => $data['nombre_depa'],
            'estado_depa' => $data['estado_depa'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function findById(int $id)
    {
        return DB::table('departamentos')
            ->where('id', $id)
            ->first();
    }

    public function update(int $id, array $data): bool
    {
        return DB::table('departamentos')
            ->where('id', $id)
            ->update([
                'nombre_depa' => $data['nombre_depa'],
                'estado_depa' => $data['estado_depa'],
                'updated_at' => now(),
            ]) > 0;
    }

    public function deactivate(int $id): bool
    {
        return DB::table('departamentos')
            ->where('id', $id)
            ->update([
                'estado_depa' => 'inactivo',
                'updated_at' => now(),
            ]) > 0;
    }

    public function toggleStatus(int $id, string $estadoActual): string
    {
        $nuevoEstado = strtolower($estadoActual) === 'activo' ? 'inactivo' : 'activo';

        DB::table('departamentos')
            ->where('id', $id)
            ->update([
                'estado_depa' => $nuevoEstado,
                'updated_at' => now(),
            ]);

        return $nuevoEstado;
    }
}
