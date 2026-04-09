<?php

namespace App\Http\Controllers;

//use App\Models\Articulo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class formMultiPasosController extends Controller
{
    public function index(): View
    {
        /*$articulos = Articulo::latest()->get();

        return view('articulos.list', [
            'articulos' => $articulos,
        ]);*/
        $articulos = collect([
            (object) [
                'id' => 1,
                'codigo' => 'ART-001',
                'nombre' => 'Articulo Demo 1',
                'descripcion' => 'Descripcion de prueba para el articulo demo 1.',
                'marca' => 'Marca Demo',
                'categoria_texto' => 'Categoria Demo',
                'estado' => 'activo',
                'precio' => 25.50,
                'stock' => 10,
            ],
            (object)[
                'id' => 2,
                'codigo' => 'ART-002',
                'nombre' => 'Articulo Demo 2',
                'descripcion' => 'Descripcion de prueba para el articulo demo 2.',
                'marca' => 'Otra Marca',
                'categoria_texto' => 'Otra Categoria',
                'estado' => 'inactivo',
                'precio' => 40.00,
                'stock' => 5,
            ],
        ]);
        return view('articulos.list', [
            'articulos' => $articulos,
        ]);
    }

    public function create(): View
    {
        return view('articulos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules(), $this->messages());

        //Articulo::create($data);

        return redirect()
            ->route('articulos.index')
            ->with('success', 'Articulo creado correctamente.');
    }

    public function edit(Articulo $articulo): View
    {
        $articulo = (object) [ //Temporal
            'id' => $id,
            'codigo' => 'ART-00' . $id,
            'nombre' => 'Articulo Demo ' . $id,
            'descripcion' => 'Descripcion de prueba para el articulo demo ' . $id . '.',
            'marca' => 'Marca Demo',
            'categoria_texto' => 'Categoria Demo',
            'estado' => 'activo',
            'precio' => 25.50,
            'stock' => 10,
        ];//Temporal

        return view('articulos.edit', [
            'articulo' => $articulo,
        ]);
    }

    public function update(Request $request, Articulo $articulo): RedirectResponse
    {
        $data = $request->validate(
            //$this->rules($articulo->id),
            $this->rules($id), //Eliminar cuando se use el modelo
            $this->messages()
        );

        //$articulo->update($data);

        return redirect()
            ->route('articulos.index')
            ->with('success', 'Articulo actualizado correctamente.');
    }

    public function destroy(Articulo $articulo): RedirectResponse
    {
        //$articulo->delete();

        return redirect()
            ->route('articulos.index')
            ->with('success', 'Articulo eliminado correctamente.');
    }

    private function rules(?int $articuloId = null): array
    {
        return [
            'codigo' => [
                'required',
                'string',
                'max:50',
                //Rule::unique('articulos', 'codigo')->ignore($articuloId),
            ],
            'nombre' => ['required', 'string', 'min:3', 'max:150'],
            'descripcion' => ['required', 'string', 'min:10', 'max:1000'],
            'marca' => ['required', 'string', 'min:2', 'max:100'],
            'categoria_texto' => ['required', 'string', 'min:3', 'max:100'],
            'estado' => ['required', Rule::in(['activo', 'inactivo'])],
            'precio' => ['required', 'numeric', 'gt:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'wizard_step' => ['nullable', 'integer', 'min:1', 'max:4'],
        ];
    }

    private function messages(): array
    {
        return [
            'codigo.required' => 'El codigo es obligatorio.',
            'codigo.unique' => 'El codigo ya existe.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos :min caracteres.',
            'descripcion.required' => 'La descripcion es obligatoria.',
            'descripcion.min' => 'La descripcion debe tener al menos :min caracteres.',
            'marca.required' => 'La marca es obligatoria.',
            'marca.min' => 'La marca debe tener al menos :min caracteres.',
            'categoria_texto.required' => 'La categoria es obligatoria.',
            'categoria_texto.min' => 'La categoria debe tener al menos :min caracteres.',
            'estado.required' => 'El estado es obligatorio.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser numerico.',
            'precio.gt' => 'El precio debe ser mayor a 0.',
            'stock.required' => 'El stock es obligatorio.',
            'stock.integer' => 'El stock debe ser un numero entero.',
        ];
    }

    public function agendaData(){
        $data = collect([
            [   'id' => 1,
                'fecha_registro' => '2026-04-02',
                'departamento' => 'Recursos Humanos',
                'nombre_apellido' => 'Juan Perez Gomez',
                'periodo' => '2026-04-02 al 2026-04-08',
                'horario_trabajo' => '10:15 al 13:15',
            ],
            [
                'id' => 2,
                'fecha_registro' => '2026-04-03',
                'departamento' => 'Sistemas',
                'nombre_apellido' => 'María Lopez',
                'periodo' => '2026-04-03 al 2026-04-10',
                'horario_trabajo' => '08:00 al 12:00',
            ],
        ]);

        return DataTables::of($data)
            ->addColumn('acciones', function($row) {
                return '<button type="button" class="btn btn-warning btn-sm btn-editar-agenda" data-id="' . $row['id'] . '">Editar</button>';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }
}
