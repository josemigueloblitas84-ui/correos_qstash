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
                return '
                    <button type="button" class="btn btn-warning btn-sm btn-editar" data-id="' . $row->id . '">Editar</button>
                    <button type="button" class="btn btn-danger btn-sm btn-eliminar" data-id="' . $row->id . '">Eliminar</button>
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
            'id' => $id,
        ]);
    }

    public function show($id): JsonResponse
    {
        $tipo = $this->tipoPersonalService->findById((int) $id);

        if (!$tipo) {
            return response()->json([
                'status' => false,
                'message' => 'El tipo de personal no existe.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $tipo,
        ]);
    }

    public function update(TipoPersonalUpdateRequest $request, $id): JsonResponse
    {
        $tipo = $this->tipoPersonalService->findById((int) $id);

        if (!$tipo) {
            return response()->json([
                'status' => false,
                'message' => 'El tipo de personal no existe.',
            ], 404);
        }

        $this->tipoPersonalService->update((int) $id, $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Tipo de personal actualizado correctamente.',
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $tipo = $this->tipoPersonalService->findById((int) $id);

        if (!$tipo) {
            return response()->json([
                'status' => false,
                'message' => 'El tipo de personal no existe.',
            ], 404);
        }

        $this->tipoPersonalService->destroy((int) $id);

        return response()->json([
            'status' => true,
            'message' => 'Tipo de personal eliminado correctamente.',
        ]);
    }
}
