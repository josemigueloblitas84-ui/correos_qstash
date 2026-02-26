<?php

namespace App\Http\Controllers\Permisos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:ver roles')->only('index');
        $this->middleware('permission:crear roles')->only(['create']);
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles|min:3'
         ]);

         if ($validator->passes()) {
            $role = Role::create(['name' => $request->name]);

            if (!empty($request->permisos)) {
               foreach ($request->permisos as $name) {
                  $role->givePermissionTo($name);
               }
            }

            return redirect()->route('roles.index')->with('success', 'Rol creado exitosamente');
         } else {
            return redirect()->route('roles.create')->withInput()->withErrors($validator);
         }
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

    public function update($id, Request $request)
    {
        $role = Role::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles,name,'.$id.',id'
         ]);

         if ($validator->passes()) {

            $role->name = $request->name;
            $role->save();

            if (!empty($request->permisos)) {
             $role->syncPermissions($request->permisos);
            } else {
                $role->syncPermissions([]);
            }


            return redirect()->route('roles.index')->with('success', 'Rol actualizado exitosamente');
         } else {
            return redirect()->route('roles.edit', $id)->withInput()->withErrors($validator);
         }

    }

    public function destroy(Request $request)
    {
        $id = $request->id;
        $role = Role::find($id);

        if ($role == null) {
            session()->flash('error', 'Rol no encontrado');
            return response()->json(['status' => false]);
        }

        $role->delete();

        session()->flash('success', 'Rol eliminado exitosamente');
        return response()->json(['status' => true]);
    }
}
