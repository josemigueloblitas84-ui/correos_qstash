<?php

namespace App\Services\Layout;

use Illuminate\Support\Facades\DB;

class UserUbicacionService
{
    public function getAuthUserUbicacion(): ?object
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        return DB::table('users')
            ->leftJoin('instituciones', 'users.institucion_id', '=', 'instituciones.id')
            ->leftJoin('sedes', 'users.sede_id', '=', 'sedes.id')
            ->where('users.id', $user->id)
            ->select(
                'users.id',
                'users.name',
                'instituciones.nombre as institucion_nombre',
                'sedes.nombre as sede_nombre'
            )
            ->first();
    }
}
