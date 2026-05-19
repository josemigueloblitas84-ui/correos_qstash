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

        if (! tenancy()->initialized) {
            return (object) [
                'id' => $user->id,
                'name' => $user->name,
                'institucion_nombre' => 'Administracion central',
                'sede_nombre' => null,
            ];
        }

        $ubicacion = DB::table('users')
            ->leftJoin('sedes', 'users.sede_id', '=', 'sedes.id')
            ->where('users.id', $user->id)
            ->select(
                'users.id',
                'users.name',
                'sedes.nombre as sede_nombre'
            )
            ->first();

        if ($ubicacion) {
            $ubicacion->institucion_nombre = tenant('nombre') ?? tenant('id');
        }

        return $ubicacion;
    }
}
