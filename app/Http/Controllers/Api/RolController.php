<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Permisos\RolService;
use Spatie\Permission\Models\Role;
use App\Http\Requests\Permisos\RolStoreRequest;
use App\Http\Requests\Permisos\RolUpdateRequest;

class RolController extends Controller
{
    protected $rolService;

    public function __construct(RolService $rolService)
    {
        $this->middleware('permission:ver roles')->only('index','show');
        $this->middleware('permission:crear roles')->only('store');
        $this->middleware('permission:editar roles')->only('update');
        $this->middleware('permission:eliminar roles')->only('destroy');
        $this->rolService = $rolService;
    }

    //Listar
    public function index()
    {
        $roles = Role::with(['permissions'])
            ->latest()
            ->paginate(5);
        return response()->json($roles);
    }
    
    //Crear
    public function store(RolStoreRequest $request)
    {
        $role = $this->rolService->storeRol($request);
        return response()->json(['message' => 'Rol creado correctamente',
            'data' => $role
        ], 201);
    }
    //Mostrar
    public function show(Role $role)
    {
        $role->load(['permissions']);

        return response()->json($role);
    }

    //Actualizar
    public function update(RolUpdateRequest $request, Role $role)
    {
        $role = $this->rolService->updateRol($role, $request);
        return response()->json(['message' => 'Rol actualizado correctamente',
            'data' => $role
        ]);
    }

    //Eliminar
    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(["message" => "Rol eliminado correctamente"]);
    }
}
