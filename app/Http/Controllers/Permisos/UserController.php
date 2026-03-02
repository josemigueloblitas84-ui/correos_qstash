<?php

namespace App\Http\Controllers\Permisos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    /*public function __construct()
    {
        $this->middleware('permission:ver usuarios')->only('index');
        // $this->middleware('permission:crear permisos')->only(['create']);
        $this->middleware('permission:editar usuarios')->only(['edit']);
        // $this->middleware('permission:eliminar permisos')->only('destroy');
    }*/

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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
