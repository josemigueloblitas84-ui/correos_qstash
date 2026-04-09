<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Permisos\PermisoService;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\Permisos\PermisoStoreRequest;
use App\Http\Requests\Permisos\PermisoUpdateRequest;

class PermisoController extends Controller
{
    protected $permisoService;

    public function __construct(PermisoService $permisoService)
    {
        $this->middleware('permission:ver permisos')->only('index','show');
        $this->middleware('permission:crear permisos')->only('store');
        $this->middleware('permission:editar permisos')->only('update');
        $this->middleware('permission:eliminar permisos')->only('destroy');
        $this->permisoService = $permisoService;
    }

    //Listar
    public function index()
    {
        $permisos = Permission::latest()->paginate(5);
        return response()->json($permisos);
    }

    //Crear
    public function store(PermisoStoreRequest $request)
    {
        $permiso = $this->permisoService->storePermiso($request);
        return response()->json(['message' => 'Permiso creado correctamente',
            'data' => $permiso
        ], 201);
    }

    //Mostrar
    public function show(Permission $permission)
    {
        return response()->json($permission);
    }

    //Actualizar
    public function update(PermisoUpdateRequest $request, Permission $permission)
    {
        $permiso = $this->permisoService->updatePermiso($permission, $request);
        return response()->json(['message' => 'Permiso actualizado correctamente',
            'data' => $permiso
        ]);
    }

    //Eliminar
    public function destroy(Permission $permission)
    {
        $permission->delete();
        return response()->json(["message" => "Permiso eliminado correctamente"]);
    }
}
