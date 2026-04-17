<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfiguracionSistemaUpdateRequest;
use App\Services\ConfiguracionSistemaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ConfiguracionSistemaController extends Controller
{
    public function __construct(
        protected ConfiguracionSistemaService $configuracionSistemaService
    ) {
        $this->middleware('permission:configurar sistema')->only(['edit', 'update']);
    }

    public function edit(): View
    {
        $configuracion = $this->configuracionSistemaService->getOrCreate();
        $presentacion = $this->configuracionSistemaService->getPresentationData();

        return view('configuracion_sistema.edit', [
            'configuracion' => $configuracion,
            'presentacion' => $presentacion,
        ]);
    }

    public function update(ConfiguracionSistemaUpdateRequest $request): RedirectResponse
    {
        $this->configuracionSistemaService->update($request->validated());

        return redirect()
            ->route('configuracion-sistema.edit')
            ->with('success', 'La configuracion del sistema se actualizo correctamente.');
    }
}
