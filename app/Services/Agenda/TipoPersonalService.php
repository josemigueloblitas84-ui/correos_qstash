<?php

namespace App\Services\Agenda;

use Illuminate\Support\Facades\DB;
use App\Services\Support\ActivityLogger;

class TipoPersonalService
{
    public function getAllForDataTable()
    {
        return DB::table('tipos_personal')
            ->select('id', 'tipo', 'created_at')
            ->orderBy('id', 'desc');
    }

    public function store(array $data): int
    {
        $tipoPersonalId = DB::table('tipos_personal')->insertGetId([
            'tipo' => $data['tipo'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ActivityLogger::log(
            'Tipo de personal creado',
            [
                'attributes' => [
                    'id' => $tipoPersonalId,
                    'tipo' => $data['tipo'],
                ],
            ],
            null,
            null,
            'tipos_personal',
            'created'
        );

        return $tipoPersonalId;
    }

    public function findById(int $id)
    {
        return DB::table('tipos_personal')
            ->where('id', $id)
            ->first();
    }

    public function update(int $id, array $data): bool
    {
        $tipoPersonal = $this->findById($id);

        $updated = DB::table('tipos_personal')
            ->where('id', $id)
            ->update([
                'tipo' => $data['tipo'],
                'updated_at' => now(),
            ]) > 0;
        if ($updated && $tipoPersonal) {
            ActivityLogger::log(
                'Tipo de personal actualizado',
                [
                    'old' => [
                        'id' => $tipoPersonal->id,
                        'tipo' => $tipoPersonal->tipo,
                    ],
                    'attributes' => [
                        'id' => $id,
                        'tipo' => $data['tipo'],
                    ],
                ],
                null,
                null,
                'tipos_personal',
                'updated'
            );
        }

        return $updated;
    }

    public function destroy(int $id): bool
    {
        $tipoPersonal = $this->findById($id);
        $deleted = DB::table('tipos_personal')
            ->where('id', $id)
            ->delete() > 0;

        if ($deleted && $tipoPersonal) {
            ActivityLogger::log(
                'Tipo de personal eliminado',
                [
                    'old' => [
                        'id' => $tipoPersonal->id,
                        'tipo' => $tipoPersonal->tipo,
                    ],
                ],
                null,
                null,
                'tipos_personal',
                'deleted'
            );
        }

        return $deleted;
    }

    public function getForSelect()
    {
        return DB::table('tipos_personal')
            ->select('id', 'tipo')
            ->orderBy('tipo', 'asc')
            ->get();
    }
}
