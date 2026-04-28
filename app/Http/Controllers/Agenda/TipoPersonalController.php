<?php

namespace App\Http\Controllers\Agenda;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agenda\TipoPersonalStoreRequest;
use App\Http\Requests\Agenda\TipoPersonalUpdateRequest;
use App\Services\Agenda\TipoPersonalService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TipoPersonalController extends Controller
{
    protected TipoPersonalService $tipoPersonalService;

    public function __construct(TipoPersonalService $tipoPersonalService)
    {
        $this->middleware('permission:ver tipos de personal')->only([
            'index',
            'data',
            'show',
            'store',
            'update',
            'destroy',
        ]);

        $this->tipoPersonalService = $tipoPersonalService;
    }

    public function index(): View
    {
        return view('tipos-personal.list');
    }

    public function data()
    {
        $tipos = $this->tipoPersonalService->getAllForDataTable();

        return DataTables::of($tipos)
            ->addColumn('acciones', function ($row) {
                $encryptedId = e(encrypt_id((int) $row->id));

                return '
                    <button type="button" class="btn btn-warning btn-sm btn-editar" data-id="' . $encryptedId . '">Editar</button>
                    <button type="button" class="btn btn-danger btn-sm btn-eliminar" data-id="' . $encryptedId . '">Eliminar</button>
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

    public function store(TipoPersonalStoreRequest $request): JsonResponse
    {
        $id = $this->tipoPersonalService->store($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Tipo de personal creado correctamente.',
            'id' => encrypt_id($id),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $idTipoPersonal = decrypt_id($id);
        $tipo = $this->tipoPersonalService->findById((int) $idTipoPersonal);

        if (! $tipo) {
            return response()->json([
                'status' => false,
                'message' => 'El tipo de personal no existe.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => encrypt_id((int) $tipo->id),
                'tipo' => $tipo->tipo,
                'created_at' => $tipo->created_at,
            ],
        ]);
    }

    public function update(TipoPersonalUpdateRequest $request, string $id): JsonResponse
    {
        $idTipoPersonal = decrypt_id($id);
        $tipo = $this->tipoPersonalService->findById((int) $idTipoPersonal);

        if (! $tipo) {
            return response()->json([
                'status' => false,
                'message' => 'El tipo de personal no existe.',
            ], 404);
        }

        $this->tipoPersonalService->update((int) $idTipoPersonal, $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Tipo de personal actualizado correctamente.',
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $idTipoPersonal = decrypt_id($id);
        $tipo = $this->tipoPersonalService->findById((int) $idTipoPersonal);

        if (! $tipo) {
            return response()->json([
                'status' => false,
                'message' => 'El tipo de personal no existe.',
            ], 404);
        }

        $this->tipoPersonalService->destroy((int) $idTipoPersonal);

        return response()->json([
            'status' => true,
            'message' => 'Tipo de personal eliminado correctamente.',
        ]);
    }
}
