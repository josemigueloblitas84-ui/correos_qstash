<?php

namespace App\Services\Permisos;

use Spatie\Permission\Models\Permission;
use App\Http\Requests\Permisos\PermisoStoreRequest;
use App\Http\Requests\Permisos\PermisoUpdateRequest;
use App\Services\Support\ActivityLogger;

class PermisoService
{
    public function storePermiso(PermisoStoreRequest $request): Permission
    {
        $permiso = Permission::create([
            'name' => $request->name,
        ]);

        ActivityLogger::log(
            'Permiso creado',
            [
                'permission' => [
                    'id' => $permiso->id,
                    'name' => $permiso->name,
                ],
            ],
            $permiso,
            logName: 'permisos',
            event: 'created'
        );

        return $permiso;
    }

    public function updatePermiso(Permission $permiso, PermisoUpdateRequest $request): Permission
    {
        $before = [
            'name' => $permiso->name,
        ];

        $permiso->name = $request->name;
        $permiso->save();

        ActivityLogger::log(
            'Permiso actualizado',
            [
                'old' => $before,
                'attributes' => [
                    'name' => $permiso->name,
                ],
            ],
            $permiso,
            logName: 'permisos',
            event: 'updated'
        );

        return $permiso;
    }
}
