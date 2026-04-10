<?php

namespace App\Services\Permisos;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Permisos\UserRequest;
use App\Http\Requests\Permisos\UserUpdateRequest;
use App\Services\Support\ActivityLogger;

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
        $usuario->departamento_id = $request->departamento_id;
        $usuario->tipo_personal_id = $request->tipo_personal_id;
        $usuario->save();

        // $usuario->syncRoles([]);
        $usuario->load('roles');

        ActivityLogger::log(
            'Usuario creado',
            [
                'user' => [
                    'id' => $usuario->id,
                    'name' => $usuario->name,
                    'email' => $usuario->email,
                ],
                'roles' => $usuario->roles->pluck('name')->values()->all(),
            ],
            $usuario,
            logName: 'usuarios',
            event: 'created'
        );

        return $usuario;
    }

    public function userUpdate(User $usuario, UserUpdateRequest $request): User
    {
        $before = [
            'name' => $usuario->name,
            'email' => $usuario->email,
            'departamentos_id' => $usuario->departamento_id,
            'tipo_personal_id' => $usuario->tipo_personal_id,
            'roles' => $usuario->roles()->pluck('name')->values()->all(),
        ];

        $usuario->name = $request->name;
        $usuario->email = $request->email;
        $usuario->departamento_id = $request->departamento_id;
        $usuario->tipo_personal_id = $request->tipo_personal_id;
        $usuario->save();

        // $usuario->syncRoles($request->role ?? []);
        $usuario->load('roles');

        ActivityLogger::log(
            'Usuario actualizado',
            [
                'old' => $before,
                'attributes' => [
                    'name' => $usuario->name,
                    'email' => $usuario->email,
                    'departamento_id' => $usuario->departamento_id,
                    'tipo_personal_id' => $usuario->tipo_personal_id,
                    'roles' => $usuario->roles->pluck('name')->values()->all(),
                ],
            ],
            $usuario,
            logName: 'usuarios',
            event: 'updated'
        );

        return $usuario;
    }
}
