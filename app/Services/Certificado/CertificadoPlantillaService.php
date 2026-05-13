<?php

namespace App\Services\Certificado;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificadoPlantillaService
{
    private const DISK = 'public';
    private const DIRECTORY = 'certificados/plantillas';

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
        ];
    }
}
