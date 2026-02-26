<?php

namespace App\Http\Controllers\Permisos;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
// use Illuminate\Routing\Controllers\HasMiddleware;
// use Illuminate\Routing\Controllers\Middleware;

class PermisosController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:ver permisos')->only('index');
        $this->middleware('permission:crear permisos')->only(['create']);
        $this->middleware('permission:editar permisos')->only(['edit']);
        $this->middleware('permission:eliminar permisos')->only('destroy');
    }

    // Este metodo mostrara la vista de permisos
    public function index()
    {
        $permisos = Permission::orderBy('created_at', 'DESC')->paginate(10);
        return view('permisos.list', [
            'permisos' => $permisos
        ]);

    }

    // Este metodo mostrara la vista de crear permisos
    public function create()
    {
        return view('permisos.create');
    }

    // Este metodo guardara el nuevo permiso en la base de datos
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:permissions|min:3',
        ]);

        if ($validator->passes()) {
            Permission::create(['name' => $request->name]);
            return redirect()->route('permisos.index')->with('success', 'Permiso creado exitosamente');
        } else {
            return redirect()->route('permisos.create')
            ->withInput()
            ->withErrors($validator);
        }
    }

    // este metodo mostrara la vista de editar permisos
    public function edit($id)
    {
        $permiso = Permission::findOrfail($id);
        return view('permisos.edit', [
            'permiso' => $permiso
        ]);
    }

    // este metodo actualizara el permiso en la base de datos
    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|unique:permissions,name,'.$id.',id',
        ]);

        if ($validator->passes()) {
            $permiso = Permission::findOrfail($id);
            $permiso->name = $request->name;
            $permiso->save();

            return redirect()->route('permisos.index')->with('success', 'Permiso actualizado exitosamente');
        } else {
            return redirect()->route('permisos.edit', $id)
            ->withInput()
            ->withErrors($validator);
        }
    }

    // este metodo eliminara el permiso de la base de datos
    public function destroy(Request $request)
    {
        $id = $request->id;
        $permiso = Permission::findOrfail($id);

        if ($permiso == null) {
            session()->flask('error', 'El permiso no existe');
            return response()->json(['status' => false]);
        }

        $permiso->delete();

        session()->flash('success', 'Permiso eliminado exitosamente');
        return response()->json(['status' => true]);
    }
}
