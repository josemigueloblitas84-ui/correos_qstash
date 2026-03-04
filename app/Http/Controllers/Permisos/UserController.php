<?php

namespace App\Http\Controllers\Permisos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:ver usuarios')->only('index');
        $this->middleware('permission:crear usuarios')->only(['create', 'store']);
        $this->middleware('permission:editar usuarios')->only(['edit', 'update']);
        $this->middleware('permission:asignar permiso especial')->only([
            'editPermisosEspeciales',
            'updatePermisosEspeciales',
        ]);
        $this->middleware('permission:eliminar usuarios')->only('destroy');
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuarios = User::latest()->paginate(5);
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombreUsuario' => 'required|min:3',
            'correoUsuario' => 'required|email|unique:users,email',
            'contrasenaUsuario' => 'required|min:8|same:confirmar_contrasenaUsuario',
            'confirmar_contrasenaUsuario' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route('usuarios.create')->withInput()->withErrors($validator);
        }

        $usuario = new User();
        $usuario->name = $request->nombreUsuario;
        $usuario->email = $request->correoUsuario;
        $usuario->password = Hash::make($request->contrasenaUsuario);
        $usuario->save();

        $usuario->syncRoles($request->role);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $usuario = User::findOrFail($id);
        $roles = Role::orderBy('name', 'asc')->get();
        $hasRoles = $usuario->roles->pluck('id');

        return view('usuarios.edit', [
            'usuario' => $usuario,
            'roles' => $roles,
            'hasRoles' => $hasRoles
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $usuario = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $id . ',id',
        ]);

        if ($validator->fails()) {
            return redirect()->route('usuarios.edit', $id)->withInput()->withErrors($validator);
        }

        $usuario->name = $request->name;
        $usuario->email = $request->email;
        $usuario->save();

        $usuario->syncRoles($request->role);

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

        $request->validate([
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        // Solo sincroniza permisos directos del usuario (NO roles)
        $usuario->syncPermissions($request->input('permisos', []));

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

        if ($usuario == null) {
            session()->flash('error', 'Usuario no encontrado');
            return response()->json(['status' => false]);
        }

        $usuario->delete();

        session()->flash('success', 'Usuario eliminado exitosamente');
        return response()->json(['status' => true]);
    }
}
