<?php

namespace App\Http\Controllers\Certificado;

use App\Http\Controllers\Controller;
use App\Http\Requests\Certificado\CertificadoPlantillaStoreRequest;
use App\Http\Requests\Certificado\CertificadoStructureStoreRequest;
use App\Services\Certificado\CertificadoPlantillaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function previewCertificate(string $archivo): JsonResponse
    {
        if (! $this->certificadoPlantillaService->structureExists($archivo)) {
            return response()->json([
                'message' => 'Primero debes guardar la estructura del certificado.',
            ], 422);
        }

        return response()->json(
            $this->certificadoPlantillaService->previewCertificateForAuthenticatedUser(
                $archivo,
                (int) auth()->id()
            )
        );
    }

    public function previewQr(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_value' => ['nullable', 'string'],
            'qr_style' => ['nullable', 'array'],
            'qr_style.foreground' => ['nullable', 'string'],
            'qr_style.background' => ['nullable', 'string'],
            'qr_style.eye' => ['nullable', 'string'],
            'qr_style.pattern' => ['nullable', 'in:round,square'],
            'qr_style.corner_frame_shape' => ['nullable', 'in:none,square,rounded,circle'],
            'qr_style.corner_dot_shape' => ['nullable', 'in:none,square,circle'],
            'qr_style.corner_top_left' => ['nullable', 'boolean'],
            'qr_style.corner_top_right' => ['nullable', 'boolean'],
            'qr_style.corner_bottom_left' => ['nullable', 'boolean'],
            'qr_style.margin' => ['nullable', 'integer', 'min:0', 'max:10'],
            'qr_style.scale' => ['nullable', 'integer', 'min:4', 'max:20'],
        ]);

        return response()->json([
            'image_src' => $this->certificadoPlantillaService->buildQrPreviewDataUri(
                (string) ($validated['qr_value'] ?? ''),
                (array) ($validated['qr_style'] ?? [])
            ),
        ]);
    }

    public function indexUserCertificates(): View
    {
        return view('certificados.user-index', $this->certificadoPlantillaService->getUserCertificateListData(
            (int) auth()->id()
        ));
    }

    public function showGenerateCertificatePage(string $archivo): View
    {
        return view('certificados.generate', [
            'archivo' => $archivo,
            'prepareCertificateUrl' => route('mis-certificados.payload', $archivo),
            'backUrl' => route('mis-certificados.index'),
        ]);
    }

    public function downloadMyCertificate(string $archivo): JsonResponse
    {
        if (! $this->certificadoPlantillaService->structureExists($archivo)) {
            return response()->json([
                'message' => 'Primero debes guardar la estructura del certificado.',
            ], 422);
        }

        $data = $this->certificadoPlantillaService->issueCertificateForAuthenticatedUser(
            $archivo,
            (int) auth()->id()
        );

        if (! empty($data['emissionId'])) {
            $data['storeIssuedPdfUrl'] = route('mis-certificados.emitidos.archivo.store', $data['emissionId']);
        }

        return response()->json([
            ...$data,
        ]);
    }

    public function storeIssuedCertificateFile(Request $request, int $emitido): JsonResponse
    {
        $validated = $request->validate([
            'archivo_pdf' => ['required', 'file', 'mimes:pdf', 'max:30720'],
            'download_file_name' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $validated['archivo_pdf'];
        $storedPath = $this->certificadoPlantillaService->storeIssuedCertificatePdf(
            $emitido,
            (int) auth()->id(),
            file_get_contents($file->getRealPath()) ?: '',
            (string) ($validated['download_file_name'] ?? $file->getClientOriginalName() ?? 'certificado.pdf')
        );

        if (! $storedPath) {
            return response()->json([
                'message' => 'No se pudo guardar el archivo emitido del certificado.',
            ], 422);
        }

        return response()->json([
            'message' => 'Certificado generado y guardado correctamente.',
            'stored_path' => $storedPath,
        ]);
    }

    public function verifyCertificate(Request $request, string $hash): View
    {
        return view(
            'certificados.verify',
            $this->certificadoPlantillaService->getVerificationData(
                $hash,
                $request->ip(),
                $request->userAgent()
            )
        );
    }

    public function showIssuedCertificateDownloadPage(int $emitido): View
    {
        return view('certificados.download-issued', [
            'payloadUrl' => route('mis-certificados.emitidos.payload', $emitido),
            'backUrl' => route('mis-certificados.index'),
        ]);
    }

    public function getIssuedCertificateDownloadPayload(int $emitido): JsonResponse
    {
        $result = $this->certificadoPlantillaService->prepareAuthenticatedIssuedCertificateDownload(
            $emitido,
            (int) auth()->id()
        );

        if (!($result['ok'] ?? false)) {
            return response()->json([
                'message' => (string) ($result['message'] ?? 'La descarga no esta disponible.'),
            ], (int) ($result['status'] ?? 410));
        }

        return response()->json($result);
    }

}
