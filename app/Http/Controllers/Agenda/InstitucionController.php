<?php

namespace App\Http\Controllers\Agenda;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agenda\InstitucionSedesUpdateRequest;
use App\Http\Requests\Agenda\InstitucionStoreRequest;
use App\Http\Requests\Agenda\InstitucionUpdateRequest;
use App\Services\Agenda\InstitucionService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class InstitucionController extends Controller
{
    protected InstitucionService $institucionService;

    public function __construct(InstitucionService $institucionService)
    {
        $this->middleware('permission:ver instituciones')->only([
            'index',
            'data',
            'show',
        ]);
        $this->middleware('permission:crear instituciones')->only([
            'store',
        ]);
        $this->middleware('permission:editar instituciones')->only([
            'update',
        ]);
        $this->middleware('permission:eliminar instituciones')->only([
            'destroy',
        ]);
        $this->middleware('permission:asignar sedes a instituciones')->only([
            'editSedes',
            'updateSedes',
        ]);

        $this->institucionService = $institucionService;
    }

    public function index(): View
    {
        return view('instituciones.list');
    }

    public function data(Request $request): JsonResponse
    {
        $this->ensureAjaxRequest($request);

        $instituciones = $this->institucionService->getAllForDataTable();
        $canAssignSedes = Gate::allows('asignar sedes a instituciones');

        return DataTables::of($instituciones)
            ->addColumn('sedes', function ($row) {
                if ((int) ($row->sedes_count ?? 0) === 0 || ! $row->sedes_nombres) {
                    return '<span class="badge bg-secondary">Sin sedes</span>';
                }

                $sedes = array_values(array_filter(explode('||', (string) $row->sedes_nombres)));
                $badges = array_map(
                    fn ($sede) => '<span class="badge bg-info text-dark me-1 mb-1">' . e($sede) . '</span>',
                    $sedes
                );

                return implode('', $badges);
            })
            ->addColumn('acciones', function ($row) use ($canAssignSedes) {
                $encryptedId = encrypt_id((int) $row->id);
                $assignButton = '';

                if ($canAssignSedes) {
                    $assignButton = '
                    <button type="button" class="btn btn-info btn-sm btn-sedes" data-id="' . $encryptedId . '">
                        Asignar Sedes
                    </button>';
                }

                return '
                    <button type="button" class="btn btn-warning btn-sm btn-editar" data-id="' . $encryptedId . '">
                        Editar
                    </button>
                    <button type="button" class="btn btn-danger btn-sm btn-eliminar" data-id="' . $encryptedId . '">
                        Eliminar
                    </button>' . $assignButton . '
                ';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at
                    ? date('d/m/Y H:i:s', strtotime($row->created_at))
                    : '';
            })
            ->rawColumns(['sedes', 'acciones'])
            ->make(true);
    }

    public function store(InstitucionStoreRequest $request): JsonResponse
    {
        $id = $this->institucionService->store($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Institucion creada correctamente.',
            'id' => encrypt_id($id),
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $idInstitucion = decrypt_id($id);
        $this->ensureAjaxRequest($request);

        $institucion = $this->institucionService->findById($idInstitucion);

        if (! $institucion) {
            return response()->json([
                'status' => false,
                'message' => 'La institucion no existe.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => encrypt_id((int) $institucion->id),
                'nombre' => $institucion->nombre,
                'created_at' => $institucion->created_at,
            ],
        ]);
    }

    public function editSedes(Request $request, string $id): JsonResponse
    {
        $idInstitucion = decrypt_id($id);
        $this->ensureAjaxRequest($request);

        $assignmentData = $this->institucionService->getSedeAssignmentData($idInstitucion);
        $institucion = $assignmentData['institucion'];

        if (! $institucion) {
            return response()->json([
                'status' => false,
                'message' => 'La institucion no existe.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'institucion' => [
                    'id' => encrypt_id((int) $institucion->id),
                    'nombre' => $institucion->nombre,
                ],
                'sedes' => $assignmentData['sedes'],
            ],
        ]);
    }

    public function update(InstitucionUpdateRequest $request, string $id): JsonResponse
    {
        $idInstitucion = decrypt_id($id);
        $institucion = $this->institucionService->findById($idInstitucion);

        if (! $institucion) {
            return response()->json([
                'status' => false,
                'message' => 'La institucion no existe.',
            ], 404);
        }

        $this->institucionService->update($idInstitucion, $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Institucion actualizada correctamente.',
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->ensureAjaxRequest($request);

        $idInstitucion = decrypt_id($id);
        $institucion = $this->institucionService->findById($idInstitucion);

        if (! $institucion) {
            return response()->json([
                'status' => false,
                'message' => 'La institucion no existe.',
            ], 404);
        }

        $this->institucionService->destroy($idInstitucion);

        return response()->json([
            'status' => true,
            'message' => 'Institucion eliminada correctamente.',
        ]);
    }

    public function updateSedes(InstitucionSedesUpdateRequest $request, string $id): JsonResponse
    {
        $idInstitucion = decrypt_id($id);
        $institucion = $this->institucionService->findById($idInstitucion);

        if (! $institucion) {
            return response()->json([
                'status' => false,
                'message' => 'La institucion no existe.',
            ], 404);
        }

        $this->institucionService->syncSedes(
            $idInstitucion,
            $request->validated('sedes') ?? []
        );

        return response()->json([
            'status' => true,
            'message' => 'Sedes asignadas correctamente.',
        ]);
    }

    private function ensureAjaxRequest(Request $request): void
    {
        if (! $request->ajax()) {
            throw new HttpResponseException(
                response()->json([
                    'message' => 'No puedes acceder a esta ruta.',
                ], 403)
            );
        }
    }
}
