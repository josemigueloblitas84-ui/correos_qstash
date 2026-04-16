<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Services\Agenda\AgendaService;
use App\Services\Reportes\ReporteAgendaInformeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ReporteAgendaInformeController extends Controller
{
    public function __construct(
        protected ReporteAgendaInformeService $reporteAgendaInformeService,
        protected AgendaService $agendaService
    ) {
    }

    public function index()
    {
        return view('Reportes.reporte_agenda_informe', [
            'departamentos' => $this->reporteAgendaInformeService->getDepartamentos(),
            'canValidateInforme' => (bool) auth()->user()?->validador,
        ]);
    }

    public function data(Request $request)
    {
        if ($request->tipo_busqueda !== 'agenda') {
            return DataTables::of(collect())->make(true);
        }

        $query = $this->reporteAgendaInformeService->getAgendaQuery([
            'fecha_desde' => $request->fecha_desde,
            'fecha_hasta' => $request->fecha_hasta,
            'equipo' => $request->equipo,
            'auth_user_id' => auth()->id(),
        ]);

        return DataTables::of($query)
            ->addColumn('fecha_mostrar', function ($row) {
                return $row->fecha
                    ? date('d-m-Y', strtotime($row->fecha))
                    : '';
            })
            ->addColumn('nombre_mostrar', function ($row) {
                return $row->usuario_nombre ?? 'Sin usuario';
            })
            ->addColumn('equipo_mostrar', function ($row) {
                return $row->equipo_nombre ?? 'Sin equipo';
            })
            ->addColumn('fechas_agendadas', function ($row) {
                $desde = $row->fecha_desde ? date('d-m-Y', strtotime($row->fecha_desde)) : '';
                $hasta = $row->fecha_hasta ? date('d-m-Y', strtotime($row->fecha_hasta)) : '';

                return '<span class="badge-fecha badge-fecha-desde">' . e($desde) . '</span>
                        <span class="badge-fecha-separador">al</span>
                        <span class="badge-fecha badge-fecha-hasta">' . e($hasta) . '</span>';
            })
            ->addColumn('acciones', function ($row) {
                return '<button type="button" class="btn-accion-imprimir btn-preview-agenda" data-id="' . $row->id . '" title="Imprimir">
                            <i class="fas fa-print"></i>
                        </button>';
            })
            ->rawColumns(['fechas_agendadas', 'acciones'])
            ->make(true);
    }

    public function dataInforme(Request $request)
    {
        if ($request->tipo_busqueda !== 'informe') {
            return DataTables::of(collect())->make(true);
        }

        $canValidateInforme = (bool) auth()->user()?->validador;

        $query = $this->reporteAgendaInformeService->getInformeAgendaQuery([
            'fecha_desde' => $request->fecha_desde,
            'fecha_hasta' => $request->fecha_hasta,
            'equipo' => $request->equipo,
            'auth_user_id' => auth()->id(),
        ]);

        return DataTables::of($query)
            ->addColumn('fecha_mostrar', function ($row) {
                return $row->fecha_actividad
                    ? date('d-m-Y', strtotime($row->fecha_actividad))
                    : '';
            })
            ->addColumn('nombre_mostrar', function ($row) {
                return $row->usuario_nombre ?? 'Sin usuario';
            })
            ->addColumn('equipo_mostrar', function ($row) {
                return $row->equipo_nombre ?? 'Sin equipo';
            })
            ->addColumn('visualizar', function ($row) {
                return '<button
                            type="button"
                            class="btn-accion-imprimir btn-preview-informe"
                            data-fecha="' . e($row->fecha_actividad) . '"
                            data-usuario="' . e($row->usuario_id) . '"
                            title="Visualizar">
                            <i class="fas fa-print"></i>
                        </button>';
            })
            ->addColumn('validar', function ($row) use ($canValidateInforme) {
                if (!$canValidateInforme) {
                    return '';
                }

                if ((int) $row->validada_encargado === 1) {
                    return '<span class="estado-validada">Validada</span>';
                }

                return '<button
                            type="button"
                            class="btn-validar"
                            data-fecha="' . e($row->fecha_actividad) . '"
                            data-usuario="' . e($row->usuario_id) . '"
                            title="Validar">V</button>';
            })
            ->rawColumns(['visualizar', 'validar'])
            ->make(true);
    }

    public function previewAgenda(int $id)
    {
        $previewData = $this->agendaService->getPreviewData($id);

        if (!$previewData) {
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

    public function previewInforme(Request $request)
    {
        $request->validate([
            'fecha' => ['required', 'date'],
            'usuario_id' => ['required', 'integer'],
        ]);

        $previewData = $this->reporteAgendaInformeService->getInformePreviewData(
            $request->fecha,
            (int) $request->usuario_id
        );

        if (!$previewData) {
            abort(404, 'El informe no existe.');
        }

        $logoPath = public_path('assets/img/logoFundacionTrans.png');
        $previewData['logoDataUri'] = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        return Pdf::loadView('reportes.partials.informe-agenda-preview-document', $previewData)
            ->setPaper('a4', 'portrait')
            ->stream('informe-agenda-' . $request->fecha . '.pdf');
    }

    public function validarInforme(Request $request)
    {
        abort_unless((bool) auth()->user()?->validador, 403, 'No tienes permiso para validar informes.');

        $request->validate([
            'fecha' => ['required', 'date'],
            'usuario_id' => ['required', 'integer'],
        ]);

        $this->reporteAgendaInformeService->validateInforme(
            $request->fecha,
            (int) $request->usuario_id,
            (int) auth()->id()
        );

        return response()->json([
            'status' => true,
            'message' => 'Informe validado correctamente.',
        ]);
    }
}
