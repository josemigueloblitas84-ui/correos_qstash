<?php

namespace App\Http\Controllers\Agenda;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agenda\DepartamentoStoreRequest;
use App\Http\Requests\Agenda\DepartamentoUpdateRequest;
use App\Services\Agenda\DepartamentoService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class DepartamentoController extends Controller
{
    protected DepartamentoService $departamentoService;

    public function __construct(DepartamentoService $departamentoService)
    {
        $this->middleware('permission:ver departamentos')->only([
            'index',
            'data',
            'show',
            'store',
            'update',
            'toggleStatus',
            'destroy',
        ]);
        $this->departamentoService = $departamentoService;
    }

    public function index(): View
    {
        return view('departamentos.list');
    }

    public function data()
    {
        $departamentos = $this->departamentoService->getAllForDataTable();

        return DataTables::of($departamentos)
            ->addColumn('acciones', function ($row) {
                $estado = strtolower((string) $row->estado_depa);
                $toggleClass = $estado === 'activo' ? 'btn-danger' : 'btn-success';
                $toggleLabel = $estado === 'activo' ? 'Desactivar' : 'Activar';

                return '
                    <button type="button" class="btn btn-warning btn-sm btn-editar" data-id="' . $row->id . '">
                        Editar
                    </button>
                    <button type="button" class="btn ' . $toggleClass . ' btn-sm btn-toggle-estado" data-id="' . $row->id . '" data-estado="' . e($row->estado_depa) . '">
                        ' . $toggleLabel . '
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

    public function store(DepartamentoStoreRequest $request): JsonResponse
    {
        $id = $this->departamentoService->store($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Departamento creado correctamente.',
            'id' => $id,
        ]);
    }

    public function show($id): JsonResponse
    {
        $departamento = $this->departamentoService->findById((int) $id);

        if (!$departamento) {
            return response()->json([
                'status' => false,
                'message' => 'El departamento no existe.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $departamento,
        ]);
    }

    public function update(DepartamentoUpdateRequest $request, $id): JsonResponse
    {
        $departamento = $this->departamentoService->findById((int) $id);

        if (!$departamento) {
            return response()->json([
                'status' => false,
                'message' => 'El departamento no existe.',
            ], 404);
        }

        $this->departamentoService->update((int) $id, $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Departamento actualizado correctamente.',
        ]);
    }

    public function toggleStatus($id): JsonResponse
    {
        $departamento = $this->departamentoService->findById((int) $id);

        if (!$departamento) {
            return response()->json([
                'status' => false,
                'message' => 'El departamento no existe.',
            ], 404);
        }

        $nuevoEstado = $this->departamentoService->toggleStatus((int) $id, (string) $departamento->estado_depa);

        return response()->json([
            'status' => true,
            'message' => $nuevoEstado === 'activo'
                ? 'Departamento activado correctamente.'
                : 'Departamento desactivado correctamente.',
            'estado_depa' => $nuevoEstado,
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $departamento = $this->departamentoService->findById((int) $id);

        if (!$departamento) {
            return response()->json([
                'status' => false,
                'message' => 'El departamento no existe.',
            ], 404);
        }

        $this->departamentoService->deactivate((int) $id);

        return response()->json([
            'status' => true,
            'message' => 'Departamento desactivado correctamente.',
        ]);
    }
}
