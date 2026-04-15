<?php

namespace App\Http\Controllers\Agenda;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agenda\AgendaInformeStoreRequest;
use App\Services\Agenda\AgendaInformeService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InformeController extends Controller
{
    public function __construct(
        protected AgendaInformeService $agendaInformeService
    ) {
    }

    public function index(): View
    {
        $fecha = Carbon::today();

        $fechaTexto = ucfirst(
            $fecha->locale('es')->translatedFormat('l d \\d\\e F \\d\\e Y')
        );

        return view('agenda_informe.index', array_merge(
            $this->agendaInformeService->getFormularioData(auth()->id(), $fecha),
            [
                'fecha' => $fecha->toDateString(),
                'fechaTexto' => $fechaTexto,
            ]
        ));
    }

    public function store(AgendaInformeStoreRequest $request): RedirectResponse
    {
        $this->agendaInformeService->store(
            $request->validated(),
            auth()->id()
        );

        return redirect()
            ->route('informe-agenda.index')
            ->with('success', 'Informe guardado correctamente.');
    }

    public function storeNoProgramada(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'agenda_id' => ['required', 'integer', 'exists:agendas,id'],
            'actividad' => ['required', 'string'],
            'departamento_id' => ['required', 'integer', 'exists:departamentos,id'],
            'fecha' => ['required', 'date'],
        ]);

        $item = $this->agendaInformeService->storeNoProgramada(
            $validated,
            auth()->id()
        );

        return response()->json([
            'message' => 'Actividad no programada registrada correctamente.',
            'item' => $item,
        ], 201);
    }
}
