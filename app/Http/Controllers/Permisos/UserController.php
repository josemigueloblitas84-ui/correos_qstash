<?php

namespace App\Http\Controllers\Permisos;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Permisos\UserRequest;
use App\Http\Requests\Permisos\UserUpdateRequest;
use App\Http\Requests\Permisos\AsignarPersonalRequest;
use App\Services\Permisos\UserService;
use App\Services\Agenda\DepartamentoService;
use App\Services\Agenda\InstitucionService;
use App\Services\Agenda\TipoPersonalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    protected UserService $userService;
    protected DepartamentoService $departamentoService;
    protected InstitucionService $institucionService;
    protected TipoPersonalService $tipoPersonalService;

    public function __construct(
        UserService $userService,
        DepartamentoService $departamentoService,
        InstitucionService $institucionService,
        TipoPersonalService $tipoPersonalService
    ) {
        $this->middleware('permission:ver usuarios')->only('index');
        $this->middleware('permission:crear usuarios')->only(['create', 'store']);
        $this->middleware('permission:editar usuarios')->only(['edit', 'update', 'editRoles','updateRoles', 'editPersonalAsignado', 'updatePersonalAsignado']);
        $this->middleware('permission:crear usuarios|editar usuarios')->only('sedesPorInstitucion');
        $this->middleware('permission:asignar permiso especial')->only([
            'editPermisosEspeciales',
            'updatePermisosEspeciales',
        ]);
        $this->middleware('permission:eliminar usuarios')->only(['destroy', 'toggleStatus']);
        $this->userService = $userService;
        $this->departamentoService = $departamentoService;
        $this->institucionService = $institucionService;
        $this->tipoPersonalService = $tipoPersonalService;
    }

    public function index()
    {
        $usuarios = User::query()
            ->leftJoin('departamentos', 'users.departamento_id', '=', 'departamentos.id')
            ->leftJoin('instituciones', 'users.institucion_id', '=', 'instituciones.id')
            ->leftJoin('sedes', 'users.sede_id', '=', 'sedes.id')
            ->leftJoin('tipos_personal', 'users.tipo_personal_id', '=', 'tipos_personal.id')
            ->select(
                'users.*',
                'departamentos.nombre_depa as departamento_nombre',
                'instituciones.nombre as institucion_nombre',
                'sedes.nombre as sede_nombre',
                'tipos_personal.tipo as tipo_personal_nombre'
            )
            ->with(['roles', 'permissions'])
            ->latest('users.id')
            ->get();

        return view('usuarios.list', [
            'usuarios' => $usuarios
        ]);
    }

    public function create()
    {
        $departamentos = $this->departamentoService->getActiveForSelect();
        $instituciones = $this->institucionService->getForSelect();
        $tiposPersonal = $this->tipoPersonalService->getForSelect();

        return view('usuarios.create', [
            'departamentos' => $departamentos,
            'instituciones' => $instituciones,
            'tiposPersonal' => $tiposPersonal,
        ]);
    }

    public function store(UserRequest $request)
    {
        $this->userService->userStore($request);

        return redirect()->route('usuarios.index')->with('user_created', 'Usuario creado exitosamente');
    }

    public function edit(string $id)
    {
        $idUsuario = decrypt_id($id);
        $usuario = User::findOrFail($idUsuario);
        $roles = Role::orderBy('name', 'asc')->get();
        $departamentos = $this->departamentoService->getActiveForSelect();
        $instituciones = $this->institucionService->getForSelect();
        $tiposPersonal = $this->tipoPersonalService->getForSelect();
        $hasRoles = $usuario->roles->pluck('id');

        return view('usuarios.edit', [
            'usuario' => $usuario,
            'roles' => $roles,
            'departamentos' => $departamentos,
            'instituciones' => $instituciones,
            'tiposPersonal' => $tiposPersonal,
            'hasRoles' => $hasRoles,
            'encryptedId' => encrypt_id((int) $usuario->id),
        ]);
    }

    public function update(UserUpdateRequest $request, string $id)
    {
        $idUsuario = decrypt_id($id);
        $usuario = User::findOrFail($idUsuario);

        $this->userService->userUpdate($usuario, $request);

        return redirect()->route('usuarios.index')->with('user_updated', 'Usuario actualizado exitosamente');
    }

    public function sedesPorInstitucion(string $id): JsonResponse
    {
        $institucionId = (int) $id;
        $institucion = $this->institucionService->findById($institucionId);

        if (! $institucion) {
            return response()->json([
                'status' => false,
                'message' => 'La institucion no existe.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $this->institucionService->getSedesForSelectByInstitucion($institucionId),
        ]);
    }

    public function editPermisosEspeciales(string $id)
    {
        $idUsuario = decrypt_id($id);
        $usuario = User::findOrFail($idUsuario);
        $permisos = Permission::orderBy('name', 'asc')->get();

        $directPermissions = $usuario->getDirectPermissions()->pluck('name')->toArray();
        $rolePermissions = $usuario->getPermissionsViaRoles()->pluck('name')->toArray();

        return view('usuarios.permisos_especiales', [
            'usuario' => $usuario,
            'permisos' => $permisos,
            'directPermissions' => $directPermissions,
            'rolePermissions' => $rolePermissions,
            'encryptedId' => encrypt_id((int) $usuario->id),
        ]);
    }

    public function updatePermisosEspeciales(Request $request, string $id)
    {
        $idUsuario = decrypt_id($id);
        $usuario = User::findOrFail($idUsuario);

        $request->validate([
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        $this->userService->syncSpecialPermissions(
            $usuario,
            $request->input('permisos', [])
        );

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Permisos especiales actualizados correctamente.');
    }

    public function destroy(Request $request)
    {
        $idUsuario = decrypt_id($request->id);
        $usuario = User::findOrFail($idUsuario);

        $this->userService->deactivateUser($usuario);

        session()->flash('success', 'Usuario desactivado exitosamente');

        return response()->json(['status' => true]);
    }

    public function toggleStatus(string $id)
    {
        $idUsuario = decrypt_id($id);
        $usuario = User::findOrFail($idUsuario);

        $nuevoEstado = $this->userService->toggleStatus($usuario);

        return response()->json([
            'status' => true,
            'message' => $nuevoEstado === 1
                ? 'Usuario activado correctamente.'
                : 'Usuario desactivado correctamente.',
            'estado' => $nuevoEstado,
        ]);
    }

    public function editRoles(string $id)
    {
        $idUsuario = decrypt_id($id);
        $usuario = User::findOrFail($idUsuario);
        $roles = Role::orderBy('name', 'asc')->get();
        $currentRole = $usuario->roles->first()?->name;

        return view('usuarios.roles', [
            'usuario' => $usuario,
            'roles' => $roles,
            'currentRole' => $currentRole,
            'encryptedId' => encrypt_id((int) $usuario->id),
        ]);
    }

    public function updateRoles(Request $request, string $id)
    {
        $idUsuario = decrypt_id($id);
        $usuario = User::findOrFail($idUsuario);

        $request->validate([
            'role' => ['required', Rule::exists('roles', 'name')],
        ], [
            'role.required' => 'Debe seleccionar un rol.',
            'role.exists' => 'El rol seleccionado no es valido.',
        ]);

        $this->userService->syncUserRole($usuario, $request->role);

        return redirect()
            ->route('usuarios.index')
            ->with('role_assigned', 'Rol asignado exitosamente.');
    }

    public function editPersonalAsignado(string $id)
    {
        $idUsuario = decrypt_id($id);
        $usuario = User::findOrFail($idUsuario);

        $usuariosDisponibles = $this->userService->getAssignableUsers($usuario->id);
        $usuariosAsignados = $this->userService->getAssignedUserIds($usuario->id);

        return view('usuarios.personal_asignado', [
            'usuario' => $usuario,
            'usuariosDisponibles' => $usuariosDisponibles,
            'usuariosAsignados' => $usuariosAsignados,
            'encryptedId' => encrypt_id((int) $usuario->id),
        ]);
    }

    public function updatePersonalAsignado(AsignarPersonalRequest $request, string $id)
    {
        $idUsuario = decrypt_id($id);
        $usuario = User::findOrFail($idUsuario);

        $this->userService->syncAssignedPersonal(
            $usuario,
            $request->validated('usuarios_asignados') ?? [],
            $request->boolean('validador')
        );

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Personal asignado correctamente.');
    }
}
