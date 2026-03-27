<?php

namespace App\Http\Controllers\Permisos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\Permisos\PermisoStoreRequest;
use App\Http\Requests\Permisos\PermisoUpdateRequest;
use App\Services\Permisos\PermisoService;


class PermisosController extends Controller
{

    protected PermisoService $permisoService;

    public function __construct(PermisoService $permisoService)
    {
        $this->permisoService = $permisoService;

        $this->middleware('permission:ver permisos')->only('index');
        $this->middleware('permission:crear permisos')->only(['create', 'store']);
        $this->middleware('permission:editar permisos')->only(['edit', 'update']);
        $this->middleware('permission:eliminar permisos')->only('destroy');
    }

    // Este metodo mostrara la vista de permisos
    public function index()
    {
        return view('permisos.list', [
            'permisos' => $this->permisoService->getAllPermisos()
        ]);
    }

    // Este metodo mostrara la vista de crear permisos
    public function create()
    {
        return view('permisos.create');
    }

    // Este metodo guardara el nuevo permiso en la base de datos
    public function store(PermisoStoreRequest $request)
    {
        $this->permisoService->storePermiso($request);

        return redirect()->route('permisos.index')
            ->with('success', 'Permiso creado exitosamente');
    }

    // este metodo mostrara la vista de editar permisos
    public function edit(Permission $permiso)
    {
        return view('permisos.edit', [
            'permiso' => $permiso
        ]);
    }

    public function update(Permission $permiso, PermisoUpdateRequest $request)
    {
        $this->permisoService->updatePermiso($permiso, $request);

        return redirect()->route('permisos.index')
            ->with('success', 'Permiso actualizado exitosamente');
    }


    // este metodo eliminara el permiso de la base de datos
    public function destroy(Request $request)
    {
        if (! $this->permisoService->deleteById($request->id)) {
            session()->flash('error', 'El permiso no existe');
            return response()->json(['status' => false]);
        }

        session()->flash('success', 'Permiso eliminado exitosamente');
        return response()->json(['status' => true]);
    }
}
