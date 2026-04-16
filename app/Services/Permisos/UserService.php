<?php

namespace App\Services\Permisos;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Permisos\UserRequest;
use App\Http\Requests\Permisos\UserUpdateRequest;
use App\Services\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;

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
        $usuario->cod_estudiante = $request->cod_estudiante;
        $usuario->cantidad_horas_totales = $request->cantidad_horas_totales;
        $usuario->celular = $request->celular;
        $usuario->telefono_contacto = $request->telefono_contacto;
        $usuario->estado = 1;
        $usuario->save();

        $usuario->load('roles');

        ActivityLogger::log(
            'Usuario creado',
            [
                'user' => [
                    'id' => $usuario->id,
                    'name' => $usuario->name,
                    'email' => $usuario->email,
                    'cod_estudiante' => $usuario->cod_estudiante,
                    'cantidad_horas_totales' => $usuario->cantidad_horas_totales,
                    'celular' => $usuario->celular,
                    'telefono_contacto' => $usuario->telefono_contacto,
                    'estado' => (int) $usuario->estado,
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
            'cod_estudiante' => $usuario->cod_estudiante,
            'cantidad_horas_totales' => $usuario->cantidad_horas_totales,
            'celular' => $usuario->celular,
            'telefono_contacto' => $usuario->telefono_contacto,
            'roles' => $usuario->roles()->pluck('name')->values()->all(),
        ];

        $usuario->name = $request->name;
        $usuario->email = $request->email;
        $usuario->departamento_id = $request->departamento_id;
        $usuario->tipo_personal_id = $request->tipo_personal_id;
        $usuario->cod_estudiante = $request->cod_estudiante;
        $usuario->cantidad_horas_totales = $request->cantidad_horas_totales;
        $usuario->celular = $request->celular;
        $usuario->telefono_contacto = $request->telefono_contacto;
        $usuario->save();

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
                    'cod_estudiante' => $usuario->cod_estudiante,
                    'cantidad_horas_totales' => $usuario->cantidad_horas_totales,
                    'celular' => $usuario->celular,
                    'telefono_contacto' => $usuario->telefono_contacto,
                    'roles' => $usuario->roles->pluck('name')->values()->all(),
                ],
            ],
            $usuario,
            logName: 'usuarios',
            event: 'updated'
        );

        return $usuario;
    }

    public function deactivateUser(User $usuario): void
    {
        $estadoAnterior = (int) $usuario->estado;

        $usuario->estado = 0;
        $usuario->save();

        ActivityLogger::log(
            'Usuario desactivado',
            [
                'old' => [
                    'estado' => $estadoAnterior,
                ],
                'attributes' => [
                    'estado' => 0,
                ],
            ],
            $usuario,
            logName: 'usuarios',
            event: 'deactivated'
        );
    }

    public function toggleStatus(User $usuario): int
    {
        $estadoAnterior = (int) $usuario->estado;
        $nuevoEstado = $estadoAnterior === 1 ? 0 : 1;

        $usuario->estado = $nuevoEstado;
        $usuario->save();

        ActivityLogger::log(
            'Estado de usuario actualizado',
            [
                'old' => [
                    'estado' => $estadoAnterior,
                ],
                'attributes' => [
                    'estado' => $nuevoEstado,
                ],
            ],
            $usuario,
            logName: 'usuarios',
            event: 'status_updated'
        );

        return $nuevoEstado;
    }

    public function getAssignableUsers(int $userId)
    {
        return User::query()
            ->leftJoin('departamentos', 'users.departamento_id', '=', 'departamentos.id')
            ->leftJoin('tipos_personal', 'users.tipo_personal_id', '=', 'tipos_personal.id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'departamentos.nombre_depa as departamento_nombre',
                'tipos_personal.tipo as tipo_personal_nombre'
            )
            ->orderBy('users.name')
            ->get();
    }

    public function getAssignedUserIds(int $userId): array
    {
        return DB::table('agenda_personal_asignado')
            ->where('cod_usuario', $userId)
            ->pluck('cod_usuario_asignado')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function syncAssignedPersonal(User $usuario, array $assignedUserIds, bool $isValidator = false): void
    {
        $assignedUserIds = array_values(array_unique(array_map('intval', $assignedUserIds)));

        DB::transaction(function () use ($usuario, $assignedUserIds, $isValidator) {
            DB::table('agenda_personal_asignado')
                ->where('cod_usuario', $usuario->id)
                ->delete();

            if (!empty($assignedUserIds)) {
                $now = now();
                $rows = [];

                foreach ($assignedUserIds as $assignedUserId) {
                    $rows[] = [
                        'cod_usuario' => $usuario->id,
                        'cod_usuario_asignado' => $assignedUserId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                DB::table('agenda_personal_asignado')->insert($rows);
            }

            $usuario->validador = $isValidator;
            $usuario->save();
        });

        ActivityLogger::log(
            'Personal asignado actualizado',
            [
                'usuario_jefe' => [
                    'id' => $usuario->id,
                    'name' => $usuario->name,
                    'email' => $usuario->email,
                ],
                'usuarios_asignados' => array_values($assignedUserIds),
                'validador' => (int) $usuario->validador,
            ],
            $usuario,
            logName: 'usuarios',
            event: 'personal_assigned'
        );
    }
}
