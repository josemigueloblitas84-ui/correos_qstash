<?php

namespace App\Services\Permisos;

use Spatie\Permission\Models\Permission;
use App\Http\Requests\Permisos\PermisoStoreRequest;
use App\Http\Requests\Permisos\PermisoUpdateRequest;

class PermisoService
{
    public function getAllPermisos()
    {
        return Permission::orderBy('created_at', 'DESC')->get();
    }

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

    public function deleteById(?string $id): bool
    {
        $permiso = Permission::find($id);

        if (! $permiso) {
            return false;
        }

        $permiso->delete();

        return true;
    }
}
