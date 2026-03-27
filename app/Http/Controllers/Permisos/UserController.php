<?php

namespace App\Http\Controllers\Permisos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Http\Requests\Permisos\UserRequest;
use App\Http\Requests\Permisos\UserUpdateRequest;
use App\Http\Requests\Permisos\UserPermisosEspecialesUpdateRequest;
use App\Services\Permisos\UserService;

class UserController extends Controller
{

    protected UserService $userService;

    public function __construct(UserService $userService)
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
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuarios = User::with(['roles', 'permissions'])
            ->latest()
            ->paginate(5);

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
        return view('usuarios.create', [
            'roles' => $roles
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
        return view('usuarios.edit', $this->userService->getEditData($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, string $id)
    {
        $this->userService->userUpdateById($id, $request);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado exitosamente');
    }

    public function editPermisosEspeciales(string $id)
    {
        return view(
            'usuarios.permisos_especiales',
            $this->userService->getPermisosEspecialesData($id)
        );
    }

    public function updatePermisosEspeciales(UserPermisosEspecialesUpdateRequest $request, string $id)
    {
        $this->userService->updatePermisosEspeciales($id, $request->input('permisos', []));

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Permisos especiales actualizados correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        if (! $this->userService->deleteById($request->id)) {
            session()->flash('error', 'Usuario no encontrado');
            return response()->json(['status' => false]);
        }

        session()->flash('success', 'Usuario eliminado exitosamente');
        return response()->json(['status' => true]);
    }
}
