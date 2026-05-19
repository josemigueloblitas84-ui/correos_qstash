<?php

namespace App\Services;

use App\Models\ConfiguracionSistema;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\Support\ActivityLogger;

class ConfiguracionSistemaService
{
    private const DEFAULT_NAME = 'Fundacion UNIFRANZ';
    private const DEFAULT_LOGO = 'assets/img/logoFundacionTrans.png';
    private const DISK = 'public';
    private const MANAGED_DIRECTORY = 'configuracion-sistema';
    private const LEGACY_MANAGED_DIRECTORY = 'assets/img/configuracion-sistema';

    public function getOrCreate(): ConfiguracionSistema
    {
        if (! Schema::hasTable('configuracion_sistema')) {
            return new ConfiguracionSistema($this->getDefaultAttributes());
        }

        $configuracion = ConfiguracionSistema::query()->first();

        if($configuracion) {
            return $configuracion;
        }

        $configuracion = ConfiguracionSistema::query()->create($this->getDefaultAttributes());

        ActivityLogger::log(
            'Configuracion inicial del sistema creada',
            [
                'attributes' => [
                    'id' => $configuracion->id,
                    'nombre_institucion' => $configuracion->nombre_institucion,
                    'correo_institucional' => $configuracion->correo_institucional,
                    'celular_institucional' => $configuracion->celular_institucional,
                    'logo_principal' => $configuracion->logo_principal,
                    'logo_pdf' => $configuracion->logo_pdf,
                ],
            ],
            $configuracion,
            null,
            'configuracion_sistema',
            'created'
        );
        return $configuracion;
    }

    public function update(array $data): ConfiguracionSistema
    {
        $configuracion = $this->getOrCreate();
        $old = [
            'id' => $configuracion->id,
            'nombre_institucion' => $configuracion->nombre_institucion,
            'correo_institucional' => $configuracion->correo_institucional,
            'celular_institucional' => $configuracion->celular_institucional,
            'logo_principal' => $configuracion->logo_principal,
            'logo_pdf' => $configuracion->logo_pdf,
        ];

        $configuracion->nombre_institucion = $data['nombre_institucion'];
        $configuracion->correo_institucional = $data['correo_institucional'] ?: null;
        $configuracion->celular_institucional = $data['celular_institucional'] ?: null;

        if (($data['logo_principal'] ?? null) instanceof UploadedFile) {
            $configuracion->logo_principal = $this->storeImage(
                $data['logo_principal'],
                'logo_principal',
                $configuracion->logo_principal
            );
        }

        if (($data['logo_pdf'] ?? null) instanceof UploadedFile) {
            $configuracion->logo_pdf = $this->storeImage(
                $data['logo_pdf'],
                'logo_pdf',
                $configuracion->logo_pdf
            );
        }

        if (! $configuracion->logo_principal) {
            $configuracion->logo_principal = self::DEFAULT_LOGO;
        }

        if (! $configuracion->logo_pdf) {
            $configuracion->logo_pdf = self::DEFAULT_LOGO;
        }

        $configuracion->save();

        ActivityLogger::log(
            'Configuracion del sistema actualizada',
            [
                'old' => $old,
                'attributes' => [
                    'id' => $configuracion->id,
                    'nombre_institucion' => $configuracion->nombre_institucion,
                    'correo_institucional' => $configuracion->correo_institucional,
                    'celular_institucional' => $configuracion->celular_institucional,
                    'logo_principal' => $configuracion->logo_principal,
                    'logo_pdf' => $configuracion->logo_pdf,
                ],
            ],
            $configuracion,
            null,
            'configuracion_sistema',
            'updated'
        );

        return $configuracion->fresh();
    }

    public function getPresentationData(): array
    {
        if (! Schema::hasTable('configuracion_sistema')) {
            return $this->mapForPresentation($this->getDefaultAttributes());
        }

        $configuracion = ConfiguracionSistema::query()->first();

        if (! $configuracion) {
            return $this->mapForPresentation($this->getDefaultAttributes());
        }

        return $this->mapForPresentation($configuracion->toArray());
    }

    public function getPdfLogoDataUri(): ?string
    {
        $configuracion = $this->getPresentationData();

        if ($this->isPublicAssetPath($configuracion['logo_pdf_path'])) {
            $logoPath = public_path($configuracion['logo_pdf_path']);

            if (! File::exists($logoPath)) {
                return null;
            }

            $mime = File::mimeType($logoPath) ?: 'image/png';

            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
        }

        if (! Storage::disk(self::DISK)->exists($configuracion['logo_pdf_path'])) {
            return null;
        }

        $mime = Storage::disk(self::DISK)->mimeType($configuracion['logo_pdf_path']) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode(
            Storage::disk(self::DISK)->get($configuracion['logo_pdf_path'])
        );
    }

    private function mapForPresentation(array $data): array
    {
        $logoPrincipalPath = $data['logo_principal'] ?: self::DEFAULT_LOGO;
        $logoPdfPath = $data['logo_pdf'] ?: self::DEFAULT_LOGO;

        return [
            'nombre_institucion' => $data['nombre_institucion'] ?: self::DEFAULT_NAME,
            'logo_principal_path' => $logoPrincipalPath,
            'logo_principal_url' => $this->buildImageUrl($logoPrincipalPath),
            'logo_pdf_path' => $logoPdfPath,
            'logo_pdf_url' => $this->buildImageUrl($logoPdfPath),
            'correo_institucional' => $data['correo_institucional'] ?? null,
            'celular_institucional' => $data['celular_institucional'] ?? null,
        ];
    }

    private function storeImage(UploadedFile $file, string $prefix, ?string $currentPath): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = $prefix . '_' . now()->format('YmdHis') . '_' . Str::random(6) . '.' . $extension;

        $storedPath = $file->storeAs(self::MANAGED_DIRECTORY, $filename, self::DISK);

        if ($storedPath === false) {
            throw new \RuntimeException('No se pudo guardar la imagen en storage.');
        }

        $this->deleteManagedFile($currentPath);

        return $storedPath;
    }

    private function deleteManagedFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        $normalizedPath = str_replace('\\', '/', $path);

        if (! str_starts_with($normalizedPath, self::MANAGED_DIRECTORY . '/')) {
            if (! str_starts_with($normalizedPath, self::LEGACY_MANAGED_DIRECTORY . '/')) {
                return;
            }

            $absolutePath = public_path($normalizedPath);

            if(File::exists($absolutePath) && File::delete($absolutePath)) {
                ActivityLogger::log(
                    'Archivo de configuracion eliminado',
                    [
                        'attributes' => [
                            'path' => $normalizedPath,
                            'disk' => 'public_path',
                        ],
                    ],
                    null,
                    null,
                    'configuracion_sistema',
                    'file_deleted'
                );
            }

            return;
        }

        if (Storage::disk(self::DISK)->exists($normalizedPath) && Storage::disk(self::DISK)->delete($normalizedPath)) {
            ActivityLogger::log(
                'Archivo de configuracion eliminado',
                [
                    'attributes' => [
                        'path' => $normalizedPath,
                        'disk' => self::DISK,
                    ],
                ],
                null,
                null,
                'configuracion_sistema',
                'file_deleted'
            );
        }
    }

    private function buildImageUrl(string $path): string
    {
        if ($this->isPublicAssetPath($path)) {
            return global_asset($path);
        }

        return Storage::disk(self::DISK)->url($path);
    }

    private function isPublicAssetPath(string $path): bool
    {
        return str_starts_with($path, 'assets/');
    }

    private function getDefaultAttributes(): array
    {
        return [
            'nombre_institucion' => self::DEFAULT_NAME,
            'logo_principal' => self::DEFAULT_LOGO,
            'logo_pdf' => self::DEFAULT_LOGO,
            'correo_institucional' => null,
            'celular_institucional' => null,
        ];
    }
}
