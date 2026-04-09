<?php

namespace App\Http\Controllers\Permisos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permisos\RolStoreRequest;
use App\Http\Requests\Permisos\RolUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Services\Permisos\RolService;
use App\Services\Support\ActivityLogger;

class RolController extends Controller
{

    protected RolService $rolService;

    public function __construct(RolService $rolService)
    {
        $this->rolService = $rolService;

        $this->middleware('permission:crear roles')->only(['create', 'store']);
        $this->middleware('permission:editar roles')->only(['edit', 'update']);
        $this->middleware('permission:editar roles')->only(['edit']);
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
            ->with('success', 'Rol creado exitosamente');
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $hasPermisos = $role->permissions->pluck('name');
        $permisos = Permission::orderBy('name', 'ASC')->get();

        return view('roles.edit', [
            'permisos' => $permisos,
            'hasPermisos' => $hasPermisos,
            'role' => $role
        ]);
    }

    public function update(String $id, RolUpdateRequest $request)
    {
        $role = Role::findOrFail($id);

        $this->rolService->updateRol($role, $request);

        return redirect()->route('roles.index')
            ->with('success', 'Rol actualizado exitosamente');
    }

    public function destroy(Request $request)
    {
        $id = $request->id;
        $role = Role::find($id);

        if ($role == null) {
            session()->flash('error', 'Rol no encontrado');
            return response()->json(['status' => false]);
        }

        $properties = [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
            ],
            'permissions' => $role->permissions()->pluck('name')->values()->all(),
        ];

        $role->delete();

        ActivityLogger::log(
            'Rol eliminado',
            $properties,
            null,
            logName: 'roles',
            event: 'deleted'
        );

        session()->flash('success', 'Rol eliminado exitosamente');
        return response()->json(['status' => true]);
    }
}
