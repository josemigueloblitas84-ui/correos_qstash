<?php

namespace App\Http\Controllers\Permisos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permisos\RolStoreRequest;
use App\Http\Requests\Permisos\RolUpdateRequest;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Services\Permisos\RolService;

class RolController extends Controller
{
    protected RolService $rolService;

    public function __construct(RolService $rolService)
    {
        $this->rolService = $rolService;

        $this->middleware('permission:ver roles')->only(['index']);
        $this->middleware('permission:crear roles')->only(['create', 'store']);
        $this->middleware('permission:editar roles')->only(['edit', 'update']);
        $this->middleware('permission:eliminar roles')->only('destroy');
    }

    public function index()
    {
        $roles = Role::orderBy('name', 'ASC')->paginate(10);

        return view('roles.list', [
            'roles' => $roles
        ]);
    }

    public function create()
    {
        $permisos = Permission::orderBy('name', 'ASC')->get();

        return view('roles.create', [
            'permisos' => $permisos
        ]);
    }

    public function store(RolStoreRequest $request)
    {
        $this->rolService->storeRol($request);

        return redirect()->route('roles.index')
            ->with('role_created', 'Rol creado exitosamente');
    }

    public function edit(string $id)
    {
        $idRol = decrypt_id($id);
        $role = Role::findOrFail($idRol);
        $hasPermisos = $role->permissions->pluck('name');
        $permisos = Permission::orderBy('name', 'ASC')->get();

        return view('roles.edit', [
            'permisos' => $permisos,
            'hasPermisos' => $hasPermisos,
            'role' => $role,
            'encryptedId' => encrypt_id((int) $role->id),
        ]);
    }

    public function update(string $id, RolUpdateRequest $request)
    {
        $idRol = decrypt_id($id);
        $role = Role::findOrFail($idRol);

        $this->rolService->updateRol($role, $request);

        return redirect()->route('roles.index')
            ->with('role_updated', 'Rol actualizado exitosamente');
    }

    public function destroy(Request $request)
    {
        $idRol = decrypt_id($request->id);
        $role = Role::find($idRol);

        if ($role == null) {
            return response()->json([
                'status' => false,
                'message' => 'Rol no encontrado.',
            ], 404);
        }

        $this->rolService->destroyRol($role);

        return response()->json([
            'status' => true,
            'message' => 'Rol eliminado exitosamente.',
        ]);
    }
}
