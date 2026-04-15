<?php

namespace App\Http\Controllers;

//use App\Models\Articulo;
use App\Http\Requests\Agenda\AgendaActividadStoreRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use App\Services\Agenda\DepartamentoService;
use App\Services\Agenda\AgendaActividadService;
use App\Services\Agenda\AgendaService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Throwable;

class formMultiPasosController extends Controller
{
    protected DepartamentoService $departamentoService;
    protected AgendaService $agendaService;
    protected AgendaActividadService $agendaActividadService;

    public function __construct(
        DepartamentoService $departamentoService,
        AgendaService $agendaService,
        AgendaActividadService $agendaActividadService
    )
    {
        $this->departamentoService = $departamentoService;
        $this->agendaService = $agendaService;
        $this->agendaActividadService = $agendaActividadService;
    }

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
       $departamentos = $this->departamentoService->getActiveForSelect();

       return view('articulos.create', [
            'departamentos' => $departamentos,
            'articulo' => null,
       ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'fecha' => ['required', 'date'],
            'cod_unidad' => ['required', 'exists:departamentos,id'],
            'cod_solicitante' => ['required', 'exists:users,id'],
            'fecha_desde' => ['required', 'date'],
            'fecha_hasta' => [
                'required',
                'date',
                'after_or_equal:fecha_desde',
                function (string $attribute, mixed $value, \Closure $fail) use ($request) {
                    $fechaDesde = $request->input('fecha_desde');

                    if (! $fechaDesde || ! $value) {
                        return;
                    }

                    $desde = Carbon::parse($fechaDesde);
                    $hasta = Carbon::parse($value);

                    if (! $desde->isSameMonth($hasta)) {
                        $fail('La fecha final debe pertenecer al mismo mes que la fecha inicial.');
                    }
                },
            ],
            'hora_inicio_hora' => ['required'],
            'hora_inicio_minuto' => ['required'],
            'hora_fin_hora' => ['required'],
            'hora_fin_minuto' => ['required'],
        ], [
            'fecha.required' => 'La fecha es obligatoria.',
            'cod_unidad.required' => 'Debe seleccionar un departamento o unidad.',
            'cod_unidad.exists' => 'El departamento seleccionado no es válido.',
            'cod_solicitante.required' => 'Debe seleccionar un solicitante.',
            'cod_solicitante.exists' => 'El solicitante seleccionado no es válido.',
            'fecha_desde.required' => 'La fecha inicial es obligatoria.',
            'fecha_hasta.required' => 'La fecha final es obligatoria.',
            'fecha_hasta.after_or_equal' => 'La fecha final debe ser mayor o igual a la fecha inicial.',
            'hora_inicio_hora.required' => 'Debe seleccionar la hora de inicio.',
            'hora_inicio_minuto.required' => 'Debe seleccionar los minutos de inicio.',
            'hora_fin_hora.required' => 'Debe seleccionar la hora final.',
            'hora_fin_minuto.required' => 'Debe seleccionar los minutos finales.',
        ]);

        $agendaId = $this->agendaService->store($validated, auth()->id());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Agenda registrada correctamente.',
                'agenda_id' => $agendaId,
            ]);
        }

        return redirect()
            ->route('articulos.create')
            ->with('success', 'Agenda registrada correctamente.');
    }

    public function edit(int $articulo): View
    {
        $articulo = (object) [ //Temporal
            'id' => $articulo,
            'codigo' => 'ART-00' . $articulo,
            'nombre' => 'Articulo Demo ' . $articulo,
            'descripcion' => 'Descripcion de prueba para el articulo demo ' . $articulo . '.',
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

    public function update(Request $request, int $articulo): RedirectResponse
    {
        $data = $request->validate(
            $this->rules($articulo), // Temporal mientras se define el modelo real
            $this->messages()
        );

        //$articulo->update($data);

        return redirect()
            ->route('articulos.index')
            ->with('success', 'Articulo actualizado correctamente.');
    }

    public function destroy(int $articulo): RedirectResponse
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

    public function agendaData(): JsonResponse
    {
        $data = $this->agendaService->getAllForDataTable();

        return DataTables::of($data)
            ->addColumn('fecha_registro', function ($row) {
                return $row->fecha
                    ? date('d/m/Y', strtotime($row->fecha))
                    : '';
            })
            ->addColumn('periodo', function ($row) {
                $desde = $row->fecha_desde ? date('d/m/Y', strtotime($row->fecha_desde)) : '';
                $hasta = $row->fecha_hasta ? date('d/m/Y', strtotime($row->fecha_hasta)) : '';

                return $desde . ' al ' . $hasta;
            })
            ->addColumn('horario_trabajo', function ($row) {
                return $row->hora_desde . ' al ' . $row->hora_hasta;
            })
            ->addColumn('acciones', function ($row) {
                $fechaRegistro = $row->fecha
                    ? Carbon::parse($row->fecha)
                    : null;

                if ($fechaRegistro?->isSameDay(Carbon::today())) {
                    return '<div class="d-flex align-items-center" style="gap: 0.4rem;">'
                        . '<button type="button" class="btn btn-danger btn-sm btn-eliminar-agenda" data-action="delete-agenda" data-id="' . $row->id . '" title="Eliminar agenda">'
                        . '<i class="fas fa-trash-alt"></i>'
                        . '</button>'
                        . '<button type="button" class="btn btn-primary btn-sm btn-acceso-agenda" data-action="edit-agenda" data-id="' . $row->id . '" title="Abrir agenda">'
                        . '<i class="fas fa-plus"></i>'
                        . '</button>'
                        . '</div>';
                }

                return '<button type="button" class="btn btn-warning btn-sm btn-editar-agenda" data-action="edit-agenda" data-id="' . $row->id . '">Editar</button>';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function showAgenda(int $id): JsonResponse
    {
        $agenda = $this->agendaService->findById($id);

        if (! $agenda) {
            return response()->json([
                'message' => 'La agenda no existe.',
            ], 404);
        }

        return response()->json($agenda);
    }

    public function previewAgenda(int $id)
    {
        $previewData = $this->agendaService->getPreviewData($id);

        if (! $previewData) {
            abort(404, 'La agenda no existe.');
        }

        $logoPath = public_path('assets/img/logoFundacionTrans.png');
        $previewData['logoDataUri'] = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        return Pdf::loadView('articulos.partials.agenda-preview-document', $previewData)
            ->setPaper('a4', 'portrait')
            ->stream('agenda-' . $id . '.pdf');
    }

    public function destroyAgenda(int $id): JsonResponse
    {
        $agenda = $this->agendaService->findById($id);

        if (! $agenda) {
            return response()->json([
                'message' => 'La agenda no existe.',
            ], 404);
        }

        try {
            $this->agendaService->destroy($id);

            return response()->json([
                'message' => 'La agenda se elimino correctamente.',
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'No se pudo eliminar la agenda.',
            ], 500);
        }
    }

    public function sendAgenda(int $id): JsonResponse
    {
        $agenda = $this->agendaService->findById($id);

        if (! $agenda) {
            return response()->json([
                'message' => 'La agenda no existe.',
            ], 404);
        }

        try {
            $this->agendaService->send($id);

            return response()->json([
                'message' => 'La agenda se envio correctamente.',
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'No se pudo enviar la agenda.',
            ], 500);
        }
    }

    public function agendaActividadesData(Request $request): JsonResponse
    {
        $agendaId = $request->integer('agenda_id');
        $tipo = strtoupper((string) $request->query('tipo', 'D'));
        $data = $this->agendaActividadService->getAllForDataTable($agendaId, $tipo);

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('ejecucion', function ($row) use ($tipo) {
                if ($tipo === 'D') {
                    $desde = $row->hora_desde_actividad ?? '';
                    $hasta = $row->hora_hasta_actividad ?? '';

                    return $desde && $hasta
                        ? $desde . ' al ' . $hasta
                        : ($desde ?: $hasta);
                }

                $desde = $row->fecha_del ? date('d/m/Y', strtotime($row->fecha_del)) : '';
                $hasta = $row->fecha_hasta ? date('d/m/Y', strtotime($row->fecha_hasta)) : '';

                return $desde && $hasta
                    ? $desde . ' al ' . $hasta
                    : ($desde ?: $hasta);
            })
            ->addColumn('acciones', function ($row) {
                return '<div class="d-flex gap-2">'
                    . '<button type="button" class="btn btn-warning btn-sm btn-editar-actividad" data-action="edit-activity" data-id="' . $row->id . '">Editar</button>'
                    . '<button type="button" class="btn btn-danger btn-sm btn-eliminar-actividad" data-action="delete-activity" data-id="' . $row->id . '">Eliminar</button>'
                    . '</div>';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function showActividad(int $id): JsonResponse
    {
        $actividad = $this->agendaActividadService->findById($id);

        if (! $actividad) {
            return response()->json([
                'message' => 'La actividad no existe.',
            ], 404);
        }

        return response()->json($actividad);
    }

    public function storeActividad(AgendaActividadStoreRequest $request): JsonResponse
    {
        $actividadId = $this->agendaActividadService->store(
            $request->validated(),
            auth()->id()
        );

        return response()->json([
            'message' => 'Actividad registrada correctamente.',
            'actividad_id' => $actividadId,
        ], 201);
    }

    public function updateActividad(AgendaActividadStoreRequest $request, int $id): JsonResponse
    {
        $this->agendaActividadService->update($id, $request->validated());

        return response()->json([
            'message' => 'Actividad actualizada correctamente.',
            'actividad_id' => $id,
        ]);
    }

    public function destroyActividad(int $id): JsonResponse
    {
        $this->agendaActividadService->destroy($id);

        return response()->json([
            'message' => 'Actividad eliminada correctamente.',
        ]);
    }

    public function actividadAutocomplete(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        return response()->json(
            $this->agendaActividadService->autocomplete($term)->values()
        );
    }

    public function usuariosPorDepartamento($id): JsonResponse
    {
        $usuarios = User::query()
            ->leftJoin('tipos_personal', 'users.tipo_personal_id', '=', 'tipos_personal.id')
            ->where('users.departamento_id', $id)
            ->orderBy('users.name', 'asc')
            ->get(['users.id', 'users.name', 'tipos_personal.tipo as tipo_personal_nombre']);

        return response()->json($usuarios);
    }

}
