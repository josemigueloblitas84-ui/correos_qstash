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

        $this->middleware('permission:crear roles')->only(['create', 'store']);
        $this->middleware('permission:ver roles')->only(['edit', 'update']);
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

    public function edit(Role $role)
    {
        return view('roles.edit', $this->rolService->getEditData($role));
    }

    public function update(Role $role, RolUpdateRequest $request)
    {
        $this->rolService->updateRol($role, $request);

        return redirect()->route('roles.index')
            ->with('success', 'Rol actualizado exitosamente');
    }


    public function destroy(Request $request)
    {
        if (! $this->rolService->deleteById($request->id)) {
            session()->flash('error', 'Rol no encontrado');
            return response()->json(['status' => false]);
        }

        session()->flash('success', 'Rol eliminado exitosamente');
        return response()->json(['status' => true]);
    }
}
