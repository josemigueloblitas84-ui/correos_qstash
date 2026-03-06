<?php

namespace App\Services\Permisos;

use Spatie\Permission\Models\Role;
use App\Http\Requests\Permisos\RolStoreRequest;
use App\Http\Requests\Permisos\RolUpdateRequest;

class RolService
{

    /**
     * @param RolStoreRequest $request
     * @return Role
     */

    public function storeRol(RolStoreRequest $request): Role
    {
        $role = Role::create([
            'name' => $request->name,
        ]);

        $role->syncPermissions($request->permisos ?? []);

        return $role;
    }

    public function updateRol(Role $role, RolUpdateRequest $request): Role
    {
        $role->name = $request->name;
        $role->save();

        $role->syncPermissions($request->permisos ?? []);

        return $role;
    }
}
