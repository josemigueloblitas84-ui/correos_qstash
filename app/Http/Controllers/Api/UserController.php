<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Permisos\UserService;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\Permisos\UserRequest;
use App\Http\Requests\Permisos\UserUpdateRequest;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->middleware('permission:ver usuarios')->only('index','show');
        $this->middleware('permission:crear usuarios')->only('store');
        $this->middleware('permission:editar usuarios')->only('update');
        $this->middleware('permission:asignar permiso especial')->only('updatePermisosEspeciales');
        $this->middleware('permission:eliminar usuarios')->only('destroy');
        $this->userService = $userService;
    }

    //Listar
    public function index()
    {
        $usuarios = User::with(['roles','permissions'])
            ->latest()
            ->paginate(5);

        return response()->json($usuarios);
    }

    //Crear
    public function store(UserRequest $request)
    {
        $usuario = $this->userService->userStore($request);

        return response()->json(['message' => 'Usuario creado exitosamente',
            'data' => $usuario
        ], 201);
    }

    //Mostrar
    public function show(User $user)
    {
        $user->load(['roles', 'permissions']);

        return response()->json($user);
    }

    //Actualizar
    public function update(UserUpdateRequest $request, User $user)
    {
        $usuario = $this->userService->userUpdate($user, $request);

        return response()->json(['message' => 'Usuario actualizado correctamente',
            'data' => $usuario
        ]);
    }

    //Permisos directos Ejemplo -> http://127.0.0.1:8000/api/users/26/permisosespeciales
    public function updatePermisosEspeciales(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $request->validate([
            'permisos' => 'array',
        ]);

        $usuario->syncPermissions($request->permisos ?? []);

        return response()->json([
            "message" => "Permisos directos actualizados correctamente"
        ]);
    }

    //Eliminar
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'Usuario eliminado correctamente']);
    }
}


/*
Para usar los endopoints mediante Postamn
Bajo la URL - > PUT: http://127.0.0.1:8000/api/users/26/permisosespeciales
{
 "permisos":[]
}
*/