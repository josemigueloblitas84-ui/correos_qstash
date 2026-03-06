<?php

namespace App\Services\Permisos;

use App\Models\User;
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
}
