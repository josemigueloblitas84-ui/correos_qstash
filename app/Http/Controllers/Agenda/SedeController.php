<?php

namespace App\Http\Controllers\Agenda;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agenda\SedeStoreRequest;
use App\Http\Requests\Agenda\SedeUpdateRequest;
use App\Services\Agenda\SedeService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class SedeController extends Controller
{
    protected SedeService $sedeService;

    public function __construct(SedeService $sedeService)
    {
        $this->middleware('permission:ver sedes')->only([
            'index',
            'data',
            'show',
        ]);
        $this->middleware('permission:crear sedes')->only([
            'store',
        ]);
        $this->middleware('permission:editar sedes')->only([
            'update',
        ]);
        $this->middleware('permission:eliminar sedes')->only([
            'destroy',
        ]);

        $this->sedeService = $sedeService;
    }

    public function index(): View
    {
        return view('sedes.list');
    }

    public function data(Request $request): JsonResponse
    {
        $this->ensureAjaxRequest($request);

        $sedes = $this->sedeService->getAllForDataTable();

        return DataTables::of($sedes)
            ->addColumn('acciones', function ($row) {
                $encryptedId = encrypt_id((int) $row->id);

                return '
                    <button type="button" class="btn btn-warning btn-sm btn-editar" data-id="' . $encryptedId . '">
                        Editar
                    </button>
                    <button type="button" class="btn btn-danger btn-sm btn-eliminar" data-id="' . $encryptedId . '">
                        Eliminar
                    </button>
                ';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at
                    ? date('d/m/Y H:i:s', strtotime($row->created_at))
                    : '';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function store(SedeStoreRequest $request): JsonResponse
    {
        $id = $this->sedeService->store($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Sede creada correctamente.',
            'id' => encrypt_id($id),
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $idSede = decrypt_id($id);
        $this->ensureAjaxRequest($request);

        $sede = $this->sedeService->findById($idSede);

        if (! $sede) {
            return response()->json([
                'status' => false,
                'message' => 'La sede no existe.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => encrypt_id((int) $sede->id),
                'nombre' => $sede->nombre,
                'created_at' => $sede->created_at,
            ],
        ]);
    }

    public function update(SedeUpdateRequest $request, string $id): JsonResponse
    {
        $idSede = decrypt_id($id);
        $sede = $this->sedeService->findById($idSede);

        if (! $sede) {
            return response()->json([
                'status' => false,
                'message' => 'La sede no existe.',
            ], 404);
        }

        $this->sedeService->update($idSede, $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Sede actualizada correctamente.',
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->ensureAjaxRequest($request);

        $idSede = decrypt_id($id);
        $sede = $this->sedeService->findById($idSede);

        if (! $sede) {
            return response()->json([
                'status' => false,
                'message' => 'La sede no existe.',
            ], 404);
        }

        $this->sedeService->destroy($idSede);

        return response()->json([
            'status' => true,
            'message' => 'Sede eliminada correctamente.',
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
