<?php

namespace App\Services\Agenda;

use Illuminate\Support\Facades\DB;

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
        return DB::table('tipos_personal')->insertGetId([
            'tipo' => $data['tipo'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function findById(int $id)
    {
        return DB::table('tipos_personal')
            ->where('id', $id)
            ->first();
    }

    public function update(int $id, array $data): bool
    {
        return DB::table('tipos_personal')
            ->where('id', $id)
            ->update([
                'tipo' => $data['tipo'],
                'updated_at' => now(),
            ]) > 0;
    }

    public function destroy(int $id): bool
    {
        return DB::table('tipos_personal')
            ->where('id', $id)
            ->delete() > 0;
    }

    public function getForSelect()
    {
        return DB::table('tipos_personal')
            ->select('id', 'tipo')
            ->orderBy('tipo', 'asc')
            ->get();
    }
}
