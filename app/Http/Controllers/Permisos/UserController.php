<?php

namespace App\Http\Controllers\Permisos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;
use App\Http\Requests\Permisos\UserRequest;
use App\Http\Requests\Permisos\UserUpdateRequest;
use App\Services\Permisos\UserService;
use App\Services\Support\ActivityLogger;
use App\Services\Agenda\DepartamentoService;
use App\Services\Agenda\TipoPersonalService;

class UserController extends Controller
{

    protected UserService $userService;
    protected DepartamentoService $departamentoService;
    protected TipoPersonalService $tipoPersonalService;

    public function __construct(
        UserService $userService,
        DepartamentoService $departamentoService,
        TipoPersonalService $tipoPersonalService
    )
    {
        $this->middleware('permission:ver usuarios')->only('index');
        $this->middleware('permission:crear usuarios')->only(['create', 'store']);
        $this->middleware('permission:editar usuarios')->only(['edit', 'update']);
        $this->middleware('permission:asignar permiso especial')->only([
            'editPermisosEspeciales',
            'updatePermisosEspeciales',
        ]);
        $this->middleware('permission:eliminar usuarios')->only('destroy');
        $this->userService = $userService;
        $this->departamentoService = $departamentoService;
        $this->tipoPersonalService = $tipoPersonalService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuarios = User::query()
            ->leftJoin('departamentos', 'users.departamento_id', '=', 'departamentos.id')
            ->leftJoin('tipos_personal', 'users.tipo_personal_id', '=', 'tipos_personal.id')
            ->select(
                'users.*',
                'departamentos.nombre_depa as departamento_nombre',
                'tipos_personal.tipo as tipo_personal_nombre'
            )
            ->with(['roles', 'permissions'])
            ->latest('users.id')
            ->get();

        return view('usuarios.list', [
            'usuarios' => $usuarios
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::orderBy('name', 'asc')->get();
        $departamentos = $this->departamentoService->getActiveForSelect();
        $tiposPersonal = $this->tipoPersonalService->getForSelect();

        return view('usuarios.create', [
            'roles' => $roles,
            'departamentos' => $departamentos,
            'tiposPersonal' => $tiposPersonal,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        // Almacenar el usuario utilizando el servicio
        $this->userService->userStore($request);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado exitosamente');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $usuario = User::findOrFail($id);
        $roles = Role::orderBy('name', 'asc')->get();
        $departamentos = $this->departamentoService->getActiveForSelect();
        $tiposPersonal = $this->tipoPersonalService->getForSelect();
        $hasRoles = $usuario->roles->pluck('id');

        return view('usuarios.edit', [
            'usuario' => $usuario,
            'roles' => $roles,
            'departamentos' => $departamentos,
            'tiposPersonal' => $tiposPersonal,
            'hasRoles' => $hasRoles
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, string $id)
    {
        $usuario = User::findOrFail($id);
        $this->userService->userUpdate($usuario, $request);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado exitosamente');
    }

    public function editPermisosEspeciales(string $id)
    {
        $usuario = User::findOrFail($id);
        $permisos = Permission::orderBy('name', 'asc')->get();

        // Permisos directos (especiales)
        $directPermissions = $usuario->getDirectPermissions()->pluck('name')->toArray();

        // Permisos heredados por rol (solo para mostrar en pantalla)
        $rolePermissions = $usuario->getPermissionsViaRoles()->pluck('name')->toArray();

        return view('usuarios.permisos_especiales', [
            'usuario' => $usuario,
            'permisos' => $permisos,
            'directPermissions' => $directPermissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    public function updatePermisosEspeciales(Request $request, string $id)
    {
        $usuario = User::findOrFail($id);
        $previousPermissions = $usuario->getDirectPermissions()->pluck('name')->values()->all();

        $request->validate([
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        // Solo sincroniza permisos directos del usuario (NO roles)
        $usuario->syncPermissions($request->input('permisos', []));
        $usuario->load('permissions');

        ActivityLogger::log(
            'Permisos especiales de usuario actualizados',
            [
                'old' => [
                    'direct_permissions' => $previousPermissions,
                ],
                'attributes' => [
                    'direct_permissions' => $usuario->getDirectPermissions()->pluck('name')->values()->all(),
                ],
            ],
            $usuario,
            logName: 'usuarios',
            event: 'permissions_updated'
        );

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Permisos especiales actualizados correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $usuario = User::findOrFail($request->id);
        $properties = [
            'user' => [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email,
            ],
            'roles' => $usuario->roles()->pluck('name')->values()->all(),
        ];

        $usuario->delete();

        ActivityLogger::log(
            'Usuario eliminado',
            $properties,
            null,
            logName: 'usuarios',
            event: 'deleted'
        );

        session()->flash('success', 'Usuario eliminado exitosamente');
        return response()->json(['status' => true]);
    }
}
