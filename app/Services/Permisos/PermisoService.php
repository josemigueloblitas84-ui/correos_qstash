<?php

namespace App\Services\Permisos;

use Spatie\Permission\Models\Permission;
use App\Http\Requests\Permisos\PermisoStoreRequest;
use App\Http\Requests\Permisos\PermisoUpdateRequest;

class PermisoService
{
    public function storePermiso(PermisoStoreRequest $request): Permission
    {
        return Permission::create([
            'name' => $request->name,
        ]);
    }

    public function updatePermiso(Permission $permiso, PermisoUpdateRequest $request): Permission
    {
        $permiso->name = $request->name;
        $permiso->save();

        return $permiso;
    }
}
