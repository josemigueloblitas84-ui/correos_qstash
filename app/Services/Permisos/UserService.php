<?php

namespace App\Services\Permisos;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Permisos\UserRequest;
use App\Http\Requests\Permisos\UserUpdateRequest;

class UserService
{

    /**
     * @param UserRequest $request
     * @return User
     */

    public function userStore(UserRequest $request)
    {
        $usuario = new User();
        $usuario->name = $request->nombreUsuario;
        $usuario->email = $request->correoUsuario;
        $usuario->password = Hash::make($request->contrasenaUsuario);
        $usuario->save();

        $usuario->syncRoles($request->role);

        return $usuario;
    }

    public function userUpdate(User $usuario, UserUpdateRequest $request): User
    {
        $usuario->name = $request->name;
        $usuario->email = $request->email;
        $usuario->save();

        $usuario->syncRoles($request->role ?? []);

        return $usuario;
    }

    public function userUpdateById(string $id, UserUpdateRequest $request): User
    {
        $usuario = User::findOrFail($id);

        return $this->userUpdate($usuario, $request);
    }

    public function getEditData(string $id): array
    {
        $usuario = User::findOrFail($id);
        $roles = Role::orderBy('name', 'asc')->get();

        return [
            'usuario' => $usuario,
            'roles' => $roles,
            'hasRoles' => $usuario->roles->pluck('id'),
        ];
    }

    public function getPermisosEspecialesData(string $id): array
    {
        $usuario = User::findOrFail($id);
        $permisos = Permission::orderBy('name', 'asc')->get();

        return [
            'usuario' => $usuario,
            'permisos' => $permisos,
            'directPermissions' => $usuario->getDirectPermissions()->pluck('name')->toArray(),
            'rolePermissions' => $usuario->getPermissionsViaRoles()->pluck('name')->toArray(),
        ];
    }

    public function updatePermisosEspeciales(string $id, array $permisos = []): User
    {
        $usuario = User::findOrFail($id);
        $usuario->syncPermissions($permisos);

        return $usuario;
    }

    public function deleteById(?string $id): bool
    {
        $usuario = User::find($id);

        if (! $usuario) {
            return false;
        }

        $usuario->delete();

        return true;
    }
}
