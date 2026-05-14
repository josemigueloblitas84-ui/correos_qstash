<?php

namespace App\Http\Controllers\Certificado;

use App\Http\Controllers\Controller;
use App\Http\Requests\Certificado\CertificadoPlantillaStoreRequest;
use App\Http\Requests\Certificado\CertificadoStructureStoreRequest;
use App\Services\Certificado\CertificadoPlantillaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

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

    public function create(): View
    {
        return view('certificados.create');
    }

    public function store(CertificadoPlantillaStoreRequest $request): RedirectResponse
    {
        $this->certificadoPlantillaService->store($request->validated());

        return redirect()
            ->route('certificados.create')
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

    public function saveStructure(CertificadoStructureStoreRequest $request, string $archivo): JsonResponse
    {
        $this->certificadoPlantillaService->saveStructure($archivo, $request->validated());

        return response()->json([
            'message' => 'Estructura guardada correctamente.',
        ]);
    }

    public function downloadMyCertificate(string $archivo): Response
    {
        if (! $this->certificadoPlantillaService->structureExists($archivo)) {
            return redirect()
                ->route('certificados.plantillas.edit', $archivo)
                ->with('error', 'Primero debes guardar la estructura del certificado.');
        }

        $data = $this->certificadoPlantillaService->getCertificateDataForAuthenticatedUser(
            $archivo,
            (int) auth()->id()
        );

        $fileName = 'certificado-' . Str::slug((string) auth()->user()?->name, '-') . '.pdf';

        return Pdf::loadView('certificados.pdf', $data)
            ->setPaper($data['paper_name'], $data['paper_orientation'])
            ->download($fileName);
    }
}
