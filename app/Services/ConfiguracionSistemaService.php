<?php

namespace App\Services;

use App\Models\ConfiguracionSistema;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ConfiguracionSistemaService
{
    private const DEFAULT_NAME = 'Fundacion UNIFRANZ';
    private const DEFAULT_LOGO = 'assets/img/logoFundacionTrans.png';
    private const MANAGED_DIRECTORY = 'assets/img/configuracion-sistema';

    public function getOrCreate(): ConfiguracionSistema
    {
        if (! Schema::hasTable('configuracion_sistema')) {
            return new ConfiguracionSistema($this->getDefaultAttributes());
        }

        return ConfiguracionSistema::query()->first()
            ?? ConfiguracionSistema::query()->create($this->getDefaultAttributes());
    }

    public function update(array $data): ConfiguracionSistema
    {
        $configuracion = $this->getOrCreate();

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
        $logoPath = public_path($configuracion['logo_pdf_path']);

        if (! File::exists($logoPath)) {
            return null;
        }

        $mime = File::mimeType($logoPath) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
    }

    private function mapForPresentation(array $data): array
    {
        $logoPrincipalPath = $data['logo_principal'] ?: self::DEFAULT_LOGO;
        $logoPdfPath = $data['logo_pdf'] ?: self::DEFAULT_LOGO;

        return [
            'nombre_institucion' => $data['nombre_institucion'] ?: self::DEFAULT_NAME,
            'logo_principal_path' => $logoPrincipalPath,
            'logo_principal_url' => asset($logoPrincipalPath),
            'logo_pdf_path' => $logoPdfPath,
            'logo_pdf_url' => asset($logoPdfPath),
            'correo_institucional' => $data['correo_institucional'] ?? null,
            'celular_institucional' => $data['celular_institucional'] ?? null,
        ];
    }

    private function storeImage(UploadedFile $file, string $prefix, ?string $currentPath): string
    {
        $directory = public_path(self::MANAGED_DIRECTORY);
        File::ensureDirectoryExists($directory);

        $extension = strtolower($file->getClientOriginalExtension());
        $filename = $prefix . '_' . now()->format('YmdHis') . '_' . Str::random(6) . '.' . $extension;

        $file->move($directory, $filename);
        $this->deleteManagedFile($currentPath);

        return self::MANAGED_DIRECTORY . '/' . $filename;
    }

    private function deleteManagedFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        $normalizedPath = str_replace('\\', '/', $path);

        if (! str_starts_with($normalizedPath, self::MANAGED_DIRECTORY . '/')) {
            return;
        }

        $absolutePath = public_path($normalizedPath);

        if (File::exists($absolutePath)) {
            File::delete($absolutePath);
        }
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
