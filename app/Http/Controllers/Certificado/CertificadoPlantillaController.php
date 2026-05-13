<?php

namespace App\Http\Controllers\Certificado;

use App\Http\Controllers\Controller;
use App\Http\Requests\Certificado\CertificadoPlantillaStoreRequest;
use App\Services\Certificado\CertificadoPlantillaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CertificadoPlantillaController extends Controller
{
    public function __construct(
        protected CertificadoPlantillaService $certificadoPlantillaService
    ) {
    }

    public function index(): View
    {
        return view(
            'certificados.index',
            $this->certificadoPlantillaService->getIndexData()
        );
    }

    public function store(CertificadoPlantillaStoreRequest $request): RedirectResponse
    {
        $this->certificadoPlantillaService->store($request->validated());

        return redirect()
            ->route('certificados.index')
            ->with('success', 'Plantilla PDF subida correctamente.');
    }

    public function show(string $archivo): BinaryFileResponse
    {
        $rutaAbsoluta = $this->certificadoPlantillaService->getPlantillaAbsolutePath($archivo);

        return response()->file($rutaAbsoluta);
    }

    public function edit(string $archivo): View
    {
        return view(
            'certificados.edit',
            $this->certificadoPlantillaService->getEditData($archivo)
        );
    }
}
