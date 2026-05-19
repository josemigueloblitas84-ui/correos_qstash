<?php

namespace App\Http\Controllers\Agenda;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agenda\InstitucionAdministradorStoreRequest;
use App\Http\Requests\Agenda\InstitucionStoreRequest;
use App\Http\Requests\Agenda\InstitucionUpdateRequest;
use App\Services\Agenda\InstitucionService;
use App\Services\Central\TenantLoginLinkService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class InstitucionController extends Controller
{
    protected InstitucionService $institucionService;

    public function __construct(
        InstitucionService $institucionService,
        private TenantLoginLinkService $tenantLoginLinkService
    )
    {
        $this->middleware('permission:ver instituciones')->only([
            'index',
            'data',
            'show',
            'connect',
        ]);
        $this->middleware('permission:crear instituciones')->only([
            'store',
            'storeAdministrator',
        ]);
        $this->middleware('permission:editar instituciones')->only([
            'update',
        ]);
        $this->middleware('permission:eliminar instituciones')->only([
            'destroy',
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
        return DataTables::of($instituciones)
            ->addColumn('dominios', function ($row) {
                if (! $row->dominios) {
                    return '<span class="badge bg-secondary">Sin dominio</span>';
                }

                $domains = array_values(array_filter(explode('||', (string) $row->dominios)));
                $badges = array_map(
                    fn ($domain) => '<span class="badge bg-info text-dark me-1 mb-1">' . e($domain) . '</span>',
                    $domains
                );

                return implode('', $badges);
            })
            ->addColumn('base_datos', function ($row) {
                return e(config('tenancy.database.prefix') . $row->id . config('tenancy.database.suffix'));
            })
            ->addColumn('acciones', function ($row) {
                $domains = array_values(array_filter(explode('||', (string) $row->dominios)));
                $primaryDomain = $domains[0] ?? null;

                $administrarButton = $primaryDomain
                    ? '<button type="button" class="btn btn-success btn-sm btn-conectar" data-id="' . e($row->id) . '" data-nombre="' . e($row->nombre) . '">Conectar</button>'
                    : '<button type="button" class="btn btn-success btn-sm" disabled>Conectar</button>';

                return '
                    ' . $administrarButton . '
                    <button type="button" class="btn btn-primary btn-sm btn-administrador" data-id="' . e($row->id) . '" data-nombre="' . e($row->nombre) . '">
                        Crear administrador
                    </button>
                    <button type="button" class="btn btn-warning btn-sm btn-editar" data-id="' . e($row->id) . '">
                        Editar
                    </button>
                    <button type="button" class="btn btn-danger btn-sm btn-eliminar" data-id="' . e($row->id) . '">
                        Desactivar
                    </button>
                ';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at
                    ? date('d/m/Y H:i:s', strtotime($row->created_at))
                    : '';
            })
            ->rawColumns(['dominios', 'acciones'])
            ->make(true);
    }

    public function store(InstitucionStoreRequest $request): JsonResponse
    {
        $tenant = $this->institucionService->store($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Institucion creada correctamente.',
            'id' => $tenant->id,
        ]);
    }

    public function storeAdministrator(InstitucionAdministradorStoreRequest $request, string $id): JsonResponse
    {
        $institucion = $this->institucionService->findById($id);

        if (! $institucion) {
            return response()->json([
                'status' => false,
                'message' => 'La institucion no existe.',
            ], 404);
        }

        try {
            $administrator = $this->institucionService->createAdministrator($id, $request->validated());
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'status' => false,
                'message' => 'No se pudo crear el administrador en la base de datos de la institucion.',
            ], 500);
        }

        if (! $administrator) {
            return response()->json([
                'status' => false,
                'message' => 'No se pudo crear el administrador.',
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Administrador creado correctamente.',
            'data' => $administrator,
        ]);
    }

    public function connect(Request $request, string $id): JsonResponse
    {
        $this->ensureAjaxRequest($request);

        $institucion = $this->institucionService->findById($id);

        if (! $institucion) {
            return response()->json([
                'status' => false,
                'message' => 'La institucion no existe.',
            ], 404);
        }

        $user = Auth::user();

        if (! $user || ! method_exists($user, 'hasRole') || ! $user->hasRole('SuperAdministrador')) {
            return response()->json([
                'status' => false,
                'message' => 'Solo un SuperAdministrador central puede conectarse a un tenant.',
            ], 403);
        }

        $loginUrl = $this->tenantLoginLinkService->createLoginUrl($institucion, $user, $request);

        if (! $loginUrl) {
            return response()->json([
                'status' => false,
                'message' => 'La institucion no tiene un dominio configurado.',
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Redirigiendo al tenant.',
            'tenant' => [
                'id' => $institucion->id,
                'nombre' => $institucion->nombre,
            ],
            'redirect_url' => $loginUrl,
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $this->ensureAjaxRequest($request);

        $institucion = $this->institucionService->findById($id);

        if (! $institucion) {
            return response()->json([
                'status' => false,
                'message' => 'La institucion no existe.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $institucion->id,
                'nombre' => $institucion->nombre,
                'domain' => $this->institucionService->primaryDomain($institucion),
                'created_at' => $institucion->created_at,
            ],
        ]);
    }

    public function update(InstitucionUpdateRequest $request, string $id): JsonResponse
    {
        $institucion = $this->institucionService->findById($id);

        if (! $institucion) {
            return response()->json([
                'status' => false,
                'message' => 'La institucion no existe.',
            ], 404);
        }

        $this->institucionService->update($id, $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Institucion actualizada correctamente.',
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->ensureAjaxRequest($request);

        $institucion = $this->institucionService->findById($id);

        if (! $institucion) {
            return response()->json([
                'status' => false,
                'message' => 'La institucion no existe.',
            ], 404);
        }

        $this->institucionService->destroy($id);

        return response()->json([
            'status' => true,
            'message' => 'Institucion dada de baja correctamente.',
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
