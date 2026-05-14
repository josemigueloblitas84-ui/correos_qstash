<?php

namespace App\Services\Certificado;

use App\Models\User;
use App\Services\Dashboard\ControlHorasService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificadoPlantillaService
{
    private const DISK = 'public';
    private const DIRECTORY = 'certificados/plantillas';
    private const STRUCTURE_DISK = 'local';
    private const STRUCTURE_DIRECTORY = 'certificados/estructuras';

    public function __construct(
        protected ControlHorasService $controlHorasService
    ) {
    }

    public function getIndexData(): array
    {
        $archivos = Storage::disk(self::DISK)->files(self::DIRECTORY);

        $plantillas = collect($archivos)
            ->filter(fn (string $path) => str_ends_with(Str::lower($path), '.pdf'))
            ->map(function (string $path) {
                $nombreArchivo = basename($path);

                return [
                    'nombre_archivo' => $nombreArchivo,
                    'ruta' => $path,
                    'tamano_kb' => round(Storage::disk(self::DISK)->size($path) / 1024, 2),
                    'fecha' => date('d/m/Y H:i', Storage::disk(self::DISK)->lastModified($path)),
                    'url_ver' => route('certificados.plantillas.show', $nombreArchivo),
                    'url_editar' => route('certificados.plantillas.edit', $nombreArchivo),
                ];
            })
            ->sortByDesc('fecha')
            ->values()
            ->all();

        return [
            'plantillas' => $plantillas,
        ];
    }

    public function store(array $data): void
    {
        /** @var UploadedFile $archivo */
        $archivo = $data['archivo_pdf'];

        $nombreBase = Str::slug($data['nombre'], '_');
        $extension = $archivo->getClientOriginalExtension() ?: 'pdf';
        $nombreArchivo = now()->format('Ymd_His') . '_' . $nombreBase . '.' . $extension;

        $archivo->storeAs(self::DIRECTORY, $nombreArchivo, self::DISK);
    }

    public function getPlantillaAbsolutePath(string $archivo): string
    {
        $archivo = basename($archivo);
        $ruta = self::DIRECTORY . '/' . $archivo;

        abort_unless(Storage::disk(self::DISK)->exists($ruta), 404);

        return Storage::disk(self::DISK)->path($ruta);
    }

    public function getEditData(string $archivo): array
    {
        $archivo = basename($archivo);
        $ruta = self::DIRECTORY . '/' . $archivo;

        abort_unless(Storage::disk(self::DISK)->exists($ruta), 404);

        return [
            'archivo' => $archivo,
            'pdfUrl' => route('certificados.plantillas.show', $archivo),
            'saveStructureUrl' => route('certificados.plantillas.estructura.store', $archivo),
            'downloadMyCertificateUrl' => route('certificados.plantillas.mi-certificado', $archivo),
        ];
    }

    public function saveStructure(string $archivo, array $payload): void
    {
        $jsonPath = $this->getStructurePath($archivo);

        Storage::disk(self::STRUCTURE_DISK)->put(
            $jsonPath,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    public function structureExists(string $archivo): bool
    {
        return Storage::disk(self::STRUCTURE_DISK)->exists(
            $this->getStructurePath($archivo)
        );
    }

    public function getCertificateDataForAuthenticatedUser(string $archivo, int $userId): array
    {
        $structure = $this->getStructure($archivo);

        $usuario = User::query()
            ->select([
                'id',
                'name',
                'cod_estudiante',
            ])
            ->findOrFail($userId);

        $summary = $this->controlHorasService->getSummaryForUser($userId);

        $variables = [
            'nombre_completo' => $usuario->name ?? '',
            'ci' => $usuario->cod_estudiante ?? '',
            'horas_institucionales' => $summary['horas_validadas'] ?? '00:00:00',
        ];

        $page = $structure['page'] ?? [
            'size' => 'a4',
            'orientation' => 'horizontal',
            'canvas_width' => 1123,
            'canvas_height' => 794,
        ];

        $elements = collect($structure['elements'] ?? [])
            ->map(fn (array $element) => $this->hydrateElement($element, $variables))
            ->values()
            ->all();

        return [
            'page' => $page,
            'elements' => $elements,
            'paper_name' => $this->resolvePaperName((string) ($page['size'] ?? 'a4')),
            'paper_orientation' => $this->resolvePaperOrientation((string) ($page['orientation'] ?? 'horizontal')),
        ];
    }

    private function hydrateElement(array $element, array $variables): array
    {
        if (($element['type'] ?? '') === 'campo_dinamico') {
            $field = $element['field'] ?? '';
            $element['text'] = $variables[$field] ?? '';
        }

        return $element;
    }

    private function getStructure(string $archivo): array
    {
        $jsonPath = $this->getStructurePath($archivo);

        abort_unless(Storage::disk(self::STRUCTURE_DISK)->exists($jsonPath), 404, 'No existe una estructura guardada para esta plantilla.');

        $content = Storage::disk(self::STRUCTURE_DISK)->get($jsonPath);

        return json_decode($content, true) ?: [];
    }

    private function getStructurePath(string $archivo): string
    {
        $safeArchivo = basename($archivo);
        $baseName = pathinfo($safeArchivo, PATHINFO_FILENAME);

        return self::STRUCTURE_DIRECTORY . '/' . $baseName . '.json';
    }

    private function resolvePaperName(string $size): string
    {
        return $size === 'carta' ? 'letter' : 'a4';
    }

    private function resolvePaperOrientation(string $orientation): string
    {
        return $orientation === 'vertical' ? 'portrait' : 'landscape';
    }
}
