<?php

namespace App\Services\Permisos;

use Spatie\Permission\Models\Role;
use App\Http\Requests\Permisos\RolStoreRequest;
use App\Http\Requests\Permisos\RolUpdateRequest;
use App\Services\Support\ActivityLogger;

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
        $role->load('permissions');

        ActivityLogger::log(
            'Rol creado',
            [
                'attributes' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('name')->values()->all(),
                ],
            ],
            $role,
            logName: 'roles',
            event: 'created'
        );

        return $role;
    }

    public function updateRol(Role $role, RolUpdateRequest $request): Role
    {
        $before = [
            'name' => $role->name,
            'permissions' => $role->permissions()->pluck('name')->values()->all(),
        ];

        $role->name = $request->name;
        $role->save();

        $role->syncPermissions($request->permisos ?? []);
        $role->load('permissions');

        ActivityLogger::log(
            'Rol actualizado',
            [
                'old' => $before,
                'attributes' => [
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('name')->values()->all(),
                ],
            ],
            $role,
            logName: 'roles',
            event: 'updated'
        );

        return $role;
    }

    public function destroyRol(Role $role): void
    {
        $properties = [
            'old' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions()->pluck('name')->values()->all(),
            ],
        ];

        $role->delete();

        ActivityLogger::log(
            'Rol eliminado',
            $properties,
            $role,
            logName: 'roles',
            event: 'deleted'
        );
    }
}
