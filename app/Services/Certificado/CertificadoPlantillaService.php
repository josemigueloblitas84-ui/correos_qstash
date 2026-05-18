<?php

namespace App\Services\Certificado;

use App\Services\Certificado\Qr\CertificadoQrOptions;
use App\Services\Certificado\Qr\CertificadoQrSvgOutput;
use App\Models\User;
use App\Services\Dashboard\ControlHorasService;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificadoPlantillaService
{
    private const DISK = 'public';
    private const DIRECTORY = 'certificados/plantillas';

    public function __construct(
        protected ControlHorasService $controlHorasService
    ) {
    }

    public function getIndexData(): array
    {
        $plantillas = DB::table('certificado_plantillas')
            ->select([
                'id',
                'nombre',
                'archivo_pdf',
                'estado',
                'created_at',
            ])
            ->orderByDesc('id')
            ->get()
            ->map(function ($plantilla) {
                $nombreArchivo = basename((string) $plantilla->archivo_pdf);
                $ruta = (string) $plantilla->archivo_pdf;
                $existeArchivo = $ruta !== '' && Storage::disk(self::DISK)->exists($ruta);

                return [
                    'id' => (int) $plantilla->id,
                    'nombre' => (string) $plantilla->nombre,
                    'nombre_archivo' => $nombreArchivo,
                    'ruta' => $ruta,
                    'tamano_kb' => $existeArchivo
                        ? round(Storage::disk(self::DISK)->size($ruta) / 1024, 2)
                        : 0,
                    'fecha' => $plantilla->created_at
                        ? date('d/m/Y H:i', strtotime((string) $plantilla->created_at))
                        : '',
                    'url_ver' => route('certificados.plantillas.show', $nombreArchivo),
                    'url_editar' => route('certificados.plantillas.edit', $nombreArchivo),
                ];
            })
            ->all();

        return [
            'plantillas' => $plantillas,
        ];
    }

    public function getUserDashboardCertificateData(): array
    {
        $templatesCount = DB::table('certificado_plantillas as cp')
            ->where('cp.estado', 'activo')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('certificado_campos as cc')
                    ->whereColumn('cc.certificado_plantilla_id', 'cp.id')
                    ->where('cc.tipo', 'qr');
            })
            ->count();

        if ($templatesCount === 0) {
            return [
                'available' => false,
                'emit_url' => null,
                'templates_count' => 0,
            ];
        }

        return [
            'available' => true,
            'emit_url' => route('mis-certificados.index'),
            'templates_count' => (int) $templatesCount,
        ];
    }

    public function getUserCertificateListData(int $userId): array
    {
        $templates = DB::table('certificado_plantillas as cp')
            ->select([
                'cp.id',
                'cp.nombre',
                'cp.archivo_pdf',
                'cp.estado',
                'cp.created_at',
            ])
            ->where('cp.estado', 'activo')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('certificado_campos as cc')
                    ->whereColumn('cc.certificado_plantilla_id', 'cp.id')
                    ->where('cc.tipo', 'qr');
            })
            ->orderByDesc('cp.id')
            ->get();

        $items = $templates->map(function ($template) use ($userId) {
            $issued = DB::table('certificado_emitidos')
                ->select([
                    'id',
                    'codigo_hash',
                    'archivo_pdf',
                    'cantidad_descargas',
                    'descargado_en',
                    'fecha_emision',
                    'estado',
                ])
                ->where('certificado_plantilla_id', $template->id)
                ->where('user_id', $userId)
                ->orderByDesc('id')
                ->first();

            $archivo = basename((string) $template->archivo_pdf);
            $canDownload = $issued
                && (string) $issued->estado === 'emitido'
                && (int) ($issued->cantidad_descargas ?? 0) < 5
                && !empty($issued->archivo_pdf);

            return [
                'plantilla_id' => (int) $template->id,
                'nombre' => (string) $template->nombre,
                'archivo' => $archivo,
                'emit_url' => route('mis-certificados.emitir', $archivo),
                'download_url' => $canDownload
                    ? route('mis-certificados.emitidos.descargar', (int) $issued->id)
                    : null,
                'emitido' => (bool) $issued,
                'emitido_id' => $issued?->id ? (int) $issued->id : null,
                'codigo_hash' => $issued?->codigo_hash,
                'cantidad_descargas' => (int) ($issued->cantidad_descargas ?? 0),
                'descargas_restantes' => max(0, 5 - (int) ($issued->cantidad_descargas ?? 0)),
                'fecha_emision' => $issued?->fecha_emision,
                'estado' => $issued?->estado ?? 'pendiente',
            ];
        })->all();

        return [
            'items' => $items,
        ];
    }

    public function store(array $data): void
    {
        /** @var UploadedFile $archivo */
        $archivo = $data['archivo_pdf'];

        $nombreBase = Str::slug($data['nombre'], '_');
        $extension = $archivo->getClientOriginalExtension() ?: 'pdf';
        $nombreArchivo = now()->format('Ymd_His') . '_' . $nombreBase . '.' . $extension;
        $rutaArchivo = $archivo->storeAs(self::DIRECTORY, $nombreArchivo, self::DISK);

        DB::table('certificado_plantillas')->insert([
            'nombre' => $data['nombre'],
            'archivo_pdf' => $rutaArchivo,
            'json_estructura' => null,
            'tamano_hoja' => 'a4',
            'orientacion' => 'horizontal',
            'estado' => 'activo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function getPlantillaAbsolutePath(string $archivo): string
    {
        $plantilla = $this->findPlantillaByArchivo($archivo);
        $ruta = (string) $plantilla->archivo_pdf;

        abort_unless($ruta !== '' && Storage::disk(self::DISK)->exists($ruta), 404);

        return Storage::disk(self::DISK)->path($ruta);
    }

    public function getEditData(string $archivo): array
    {
        $plantilla = $this->findPlantillaByArchivo($archivo);
        $estructura = $this->getStructureForPlantilla((int) $plantilla->id);
        $page = $this->resolveEditorPage($estructura, $plantilla);
        $archivoBase = basename((string) $plantilla->archivo_pdf);

        return [
            'archivo' => $archivoBase,
            'pdfUrl' => route('certificados.plantillas.show', $archivoBase),
            'saveStructureUrl' => route('certificados.plantillas.estructura.store', $archivoBase),
            'previewCertificateUrl' => route('certificados.plantillas.preview', $archivoBase),
            'qrPreviewUrl' => route('certificados.qr.preview'),
            'initialStructure' => $estructura,
            'editorPage' => $page,
        ];
    }

    public function saveStructure(string $archivo, array $payload): void
    {
        $plantilla = $this->findPlantillaByArchivo($archivo);
        $page = $payload['page'] ?? [];
        $elements = array_values($payload['elements'] ?? []);

        DB::transaction(function () use ($plantilla, $payload, $page, $elements) {
            $savedElementIds = [];
            $persistedElements = [];

            foreach ($elements as $index => $element) {
                $record = $this->mapElementToFieldRecord((int) $plantilla->id, $element, $index);
                $fieldId = isset($element['db_id']) ? (int) $element['db_id'] : null;

                if ($fieldId > 0) {
                    DB::table('certificado_campos')
                        ->where('id', $fieldId)
                        ->where('certificado_plantilla_id', $plantilla->id)
                        ->update($record);

                    $savedElementIds[] = $fieldId;
                    $persistedElements[] = [
                        ...$element,
                        'db_id' => $fieldId,
                    ];
                    continue;
                }

                $newId = DB::table('certificado_campos')->insertGetId([
                    ...$record,
                    'created_at' => now(),
                ]);
                $savedElementIds[] = $newId;
                $persistedElements[] = [
                    ...$element,
                    'db_id' => $newId,
                ];
            }

            $deleteQuery = DB::table('certificado_campos')
                ->where('certificado_plantilla_id', $plantilla->id);

            if (!empty($savedElementIds)) {
                $deleteQuery->whereNotIn('id', $savedElementIds);
            }

            $deleteQuery->delete();

            DB::table('certificado_plantillas')
                ->where('id', $plantilla->id)
                ->update([
                    'json_estructura' => json_encode([
                        'page' => $page,
                        'elements' => $persistedElements,
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'tamano_hoja' => $page['size'] ?? 'a4',
                    'orientacion' => $page['orientation'] ?? 'horizontal',
                    'updated_at' => now(),
                ]);
        });
    }

    public function structureExists(string $archivo): bool
    {
        $plantilla = $this->findPlantillaByArchivo($archivo);

        if (!empty($plantilla->json_estructura)) {
            return true;
        }

        return DB::table('certificado_campos')
            ->where('certificado_plantilla_id', $plantilla->id)
            ->exists();
    }

    public function getCertificateDataForAuthenticatedUser(string $archivo, int $userId): array
    {
        $plantilla = $this->findPlantillaByArchivo($archivo);
        $structure = $this->getStructureForPlantilla((int) $plantilla->id);

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
            'horas_institucionales' => $this->formatHoursForCertificate($summary['horas_validadas'] ?? '00:00:00'),
        ];

        $page = $this->resolveEditorPage($structure, $plantilla);

        $elements = collect($structure['elements'] ?? [])
            ->map(fn (array $element) => $this->hydrateElement($element, $variables))
            ->values()
            ->all();

        return [
            'page' => $page,
            'elements' => $elements,
            'paper_name' => $this->resolvePaperName((string) ($page['size'] ?? 'a4')),
            'paper_orientation' => $this->resolvePaperOrientation((string) ($page['orientation'] ?? 'horizontal')),
            'paper_dimensions' => $this->resolvePaperDimensions((string) ($page['size'] ?? 'a4'), (string) ($page['orientation'] ?? 'horizontal')),
        ];
    }

    public function previewCertificateForAuthenticatedUser(string $archivo, int $userId): array
    {
        $usuario = $this->getCertificateUser($userId);
        $data = $this->getCertificateDataForAuthenticatedUser($archivo, $userId);
        $previewUrl = route('certificados.verificar', ['hash' => 'vista-previa']);

        $data['elements'] = collect($data['elements'])
            ->map(function (array $element) use ($previewUrl) {
                if (($element['type'] ?? '') !== 'qr') {
                    return $element;
                }

                return [
                    ...$element,
                    'qr_value' => $previewUrl,
                    'image_src' => $this->generateQrImageDataUri($previewUrl, $element['qr_style'] ?? []),
                ];
            })
            ->values()
            ->all();

        return [
            ...$data,
            'emissionId' => null,
            'verificationHash' => null,
            'verificationUrl' => null,
            'downloadFileName' => 'certificado-vista-previa-' . Str::slug((string) $usuario->name, '-') . '.pdf',
        ];
    }

    public function issueCertificateForAuthenticatedUser(string $archivo, int $userId): array
    {
        $plantilla = $this->findPlantillaByArchivo($archivo);
        $usuario = $this->getCertificateUser($userId);
        $hours = $this->resolveUserHoursForCertificate($userId);
        $data = $this->getCertificateDataForAuthenticatedUser($archivo, $userId);
        $hasQrBlock = collect($data['elements'])
            ->contains(fn (array $element) => ($element['type'] ?? '') === 'qr');

        if (! $hasQrBlock) {
            return [
                ...$data,
                'emissionId' => null,
                'verificationHash' => null,
                'verificationUrl' => null,
                'qrDownloadUrl' => null,
                'downloadFileName' => 'certificado-' . Str::slug((string) $usuario->name, '-') . '.pdf',
            ];
        }

        $existingEmission = DB::table('certificado_emitidos')
            ->select([
                'id',
                'codigo_hash',
                'archivo_pdf',
                'cantidad_descargas',
                'fecha_emision',
                'estado',
            ])
            ->where('certificado_plantilla_id', $plantilla->id)
            ->where('user_id', $usuario->id)
            ->where('estado', 'emitido')
            ->orderByDesc('id')
            ->first();

        if ($existingEmission) {
            $verificationUrl = route('certificados.verificar', ['hash' => $existingEmission->codigo_hash]);

            $data['elements'] = collect($data['elements'])
                ->map(function (array $element) use ($verificationUrl) {
                    if (($element['type'] ?? '') !== 'qr') {
                        return $element;
                    }

                    return [
                        ...$element,
                        'qr_value' => $verificationUrl,
                        'image_src' => $this->generateQrImageDataUri($verificationUrl, $element['qr_style'] ?? []),
                    ];
                })
                ->values()
                ->all();

            return [
                ...$data,
                'alreadyEmitted' => true,
                'emissionId' => (int) $existingEmission->id,
                'verificationHash' => (string) $existingEmission->codigo_hash,
                'verificationUrl' => $verificationUrl,
                'downloadFileName' => 'certificado-' . Str::slug((string) $usuario->name, '-') . '.pdf',
            ];
        }

        $hash = $this->generateCertificateHash();
        $verificationUrl = route('certificados.verificar', ['hash' => $hash]);

        $emitidoId = DB::table('certificado_emitidos')->insertGetId([
            'certificado_plantilla_id' => $plantilla->id,
            'user_id' => $usuario->id,
            'codigo_hash' => $hash,
            'nombre_generado' => $usuario->name ?? '',
            'ci_generado' => $usuario->cod_estudiante ?? '',
            'horas_generadas' => $hours,
            'archivo_pdf' => null,
            'cantidad_descargas' => 0,
            'descargado_en' => null,
            'fecha_emision' => now(),
            'estado' => 'emitido',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $data['elements'] = collect($data['elements'])
            ->map(function (array $element) use ($verificationUrl) {
                if (($element['type'] ?? '') !== 'qr') {
                    return $element;
                }

                return [
                    ...$element,
                    'qr_value' => $verificationUrl,
                    'image_src' => $this->generateQrImageDataUri($verificationUrl, $element['qr_style'] ?? []),
                ];
            })
            ->values()
            ->all();

        return [
            ...$data,
            'alreadyEmitted' => false,
            'emissionId' => $emitidoId,
            'verificationHash' => $hash,
            'verificationUrl' => $verificationUrl,
            'downloadFileName' => 'certificado-' . Str::slug((string) $usuario->name, '-') . '.pdf',
        ];
    }

    public function storeIssuedCertificatePdf(int $emitidoId, int $userId, string $pdfBinary, string $downloadFileName): ?string
    {
        $emitido = DB::table('certificado_emitidos')
            ->select(['id', 'nombre_generado', 'codigo_hash', 'user_id', 'estado'])
            ->where('id', $emitidoId)
            ->where('user_id', $userId)
            ->first();

        if (! $emitido || (string) $emitido->estado !== 'emitido') {
            return null;
        }

        $safeName = pathinfo($downloadFileName, PATHINFO_FILENAME);
        $baseName = Str::slug($safeName !== '' ? $safeName : ((string) $emitido->nombre_generado ?: 'certificado'), '-');
        $fileName = 'certificados/emitidos/' . $emitidoId . '_' . $baseName . '.pdf';

        Storage::disk(self::DISK)->put($fileName, $pdfBinary);

        DB::table('certificado_emitidos')
            ->where('id', $emitidoId)
            ->update([
                'archivo_pdf' => $fileName,
                'updated_at' => now(),
            ]);

        return $fileName;
    }

    public function prepareAuthenticatedIssuedCertificateDownload(int $emitidoId, int $userId): array
    {
        $emitido = DB::table('certificado_emitidos')
            ->join('certificado_plantillas as cp', 'cp.id', '=', 'certificado_emitidos.certificado_plantilla_id')
            ->select([
                'certificado_emitidos.id',
                'certificado_emitidos.certificado_plantilla_id',
                'certificado_emitidos.user_id',
                'certificado_emitidos.codigo_hash',
                'certificado_emitidos.nombre_generado',
                'certificado_emitidos.ci_generado',
                'certificado_emitidos.horas_generadas',
                'certificado_emitidos.archivo_pdf',
                'certificado_emitidos.estado',
                'certificado_emitidos.cantidad_descargas',
                'certificado_emitidos.descargado_en',
                'cp.archivo_pdf as plantilla_archivo',
            ])
            ->where('certificado_emitidos.id', $emitidoId)
            ->where('certificado_emitidos.user_id', $userId)
            ->first();

        if (! $emitido) {
            return [
                'ok' => false,
                'status' => 404,
                'message' => 'No se encontro el certificado emitido solicitado.',
            ];
        }

        if ((string) $emitido->estado !== 'emitido') {
            return [
                'ok' => false,
                'status' => 410,
                'message' => 'Este certificado ya no esta disponible para descarga.',
            ];
        }

        if ((int) ($emitido->cantidad_descargas ?? 0) >= 5) {
            return [
                'ok' => false,
                'status' => 410,
                'message' => 'Ya alcanzaste el maximo de 5 descargas para este certificado.',
            ];
        }

        DB::table('certificado_emitidos')
            ->where('id', $emitido->id)
            ->update([
                'cantidad_descargas' => (int) $emitido->cantidad_descargas + 1,
                'descargado_en' => now(),
                'updated_at' => now(),
            ]);

        $archivo = basename((string) $emitido->plantilla_archivo);
        $data = $this->getCertificateDataForAuthenticatedUser($archivo, $userId);
        $verificationUrl = route('certificados.verificar', ['hash' => $emitido->codigo_hash]);

        $data['elements'] = collect($data['elements'])
            ->map(function (array $element) use ($verificationUrl) {
                if (($element['type'] ?? '') !== 'qr') {
                    return $element;
                }

                return [
                    ...$element,
                    'qr_value' => $verificationUrl,
                    'image_src' => $this->generateQrImageDataUri($verificationUrl, $element['qr_style'] ?? []),
                ];
            })
            ->values()
            ->all();

        return [
            'ok' => true,
            ...$data,
            'downloadFileName' => 'certificado-' . Str::slug((string) $emitido->nombre_generado, '-') . '.pdf',
        ];
    }

    public function getVerificationData(string $hash, ?string $ip = null, ?string $userAgent = null): array
    {
        $emitido = DB::table('certificado_emitidos as ce')
            ->join('certificado_plantillas as cp', 'cp.id', '=', 'ce.certificado_plantilla_id')
            ->select([
                'ce.id',
                'ce.codigo_hash',
                'ce.nombre_generado',
                'ce.ci_generado',
                'ce.horas_generadas',
                'ce.fecha_emision',
                'ce.estado',
                'cp.nombre as plantilla_nombre',
            ])
            ->where('ce.codigo_hash', $hash)
            ->first();

        if (! $emitido) {
            return [
                'valido' => false,
                'hash' => $hash,
                'estado' => 'invalido',
                'nombre' => null,
                'ci' => null,
                'horas' => null,
                'fecha_emision' => null,
                'plantilla' => null,
            ];
        }

        $estado = (string) $emitido->estado;
        $resultado = $estado === 'emitido' ? 'valido' : 'revocado';

        DB::table('certificado_verificaciones')->insert([
            'certificado_emitido_id' => $emitido->id,
            'hash_consultado' => $hash,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'fecha_verificacion' => now(),
            'resultado' => $resultado,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'valido' => $resultado === 'valido',
            'hash' => $hash,
            'estado' => $resultado,
            'nombre' => $emitido->nombre_generado,
            'ci' => $emitido->ci_generado,
            'horas' => $emitido->horas_generadas,
            'fecha_emision' => $emitido->fecha_emision,
            'plantilla' => $emitido->plantilla_nombre,
        ];
    }

    public function buildQrPreviewDataUri(string $value, array $style = []): ?string
    {
        $value = trim($value) !== '' ? trim($value) : 'QR / Verificacion';

        return $this->generateQrImageDataUri($value, $style);
    }

    private function findPlantillaByArchivo(string $archivo): object
    {
        $archivo = basename($archivo);
        $rutaEsperada = self::DIRECTORY . '/' . $archivo;

        $plantilla = DB::table('certificado_plantillas')
            ->where('archivo_pdf', $rutaEsperada)
            ->orWhere('archivo_pdf', $archivo)
            ->latest('id')
            ->first();

        abort_unless($plantilla, 404, 'La plantilla solicitada no existe.');

        return $plantilla;
    }

    private function getStructureForPlantilla(int $plantillaId): array
    {
        $plantilla = DB::table('certificado_plantillas')
            ->select([
                'id',
                'json_estructura',
                'tamano_hoja',
                'orientacion',
            ])
            ->where('id', $plantillaId)
            ->first();

        $hasFields = DB::table('certificado_campos')
            ->where('certificado_plantilla_id', $plantillaId)
            ->exists();

        if ($hasFields) {
            return $this->buildStructureFromFields($plantillaId, $plantilla);
        }

        if (!empty($plantilla?->json_estructura)) {
            $json = json_decode((string) $plantilla->json_estructura, true);

            if (is_array($json)) {
                return $json;
            }
        }

        return $this->buildStructureFromFields($plantillaId, $plantilla);
    }

    private function buildStructureFromFields(int $plantillaId, ?object $plantilla = null): array
    {
        $plantilla ??= DB::table('certificado_plantillas')
            ->select(['tamano_hoja', 'orientacion'])
            ->where('id', $plantillaId)
            ->first();

        $elements = DB::table('certificado_campos')
            ->where('certificado_plantilla_id', $plantillaId)
            ->orderBy('orden')
            ->get()
            ->map(function ($campo) {
                $metadata = json_decode((string) ($campo->metadata ?? '{}'), true) ?: [];

                return [
                    'db_id' => (int) $campo->id,
                    'type' => $campo->tipo,
                    'left' => (float) ($campo->pos_x ?? 0),
                    'top' => (float) ($campo->pos_y ?? 0),
                    'width' => (float) ($campo->ancho ?? 0),
                    'height' => (float) ($campo->alto ?? 0),
                    'text' => $campo->texto_default,
                    'field' => $campo->valor_dinamico,
                    'qr_value' => $metadata['qr_value'] ?? '',
                    'qr_style' => $metadata['qr_style'] ?? $this->getDefaultQrStyle(),
                    'image_src' => $metadata['image_src'] ?? null,
                    'style' => [
                        'fontSize' => $campo->font_size ? (float) $campo->font_size : 24,
                        'fontFamily' => $campo->font_family ?: 'Arial',
                        'fill' => $campo->color ?: '#111111',
                        'fontWeight' => $campo->font_weight ?: 'normal',
                        'fontStyle' => $campo->font_style ?: 'normal',
                        'underline' => !empty($metadata['underline']),
                        'textAlign' => $campo->text_align ?: 'left',
                        'lineHeight' => $campo->line_height ? (float) $campo->line_height : 1.16,
                        'charSpacing' => (int) ($campo->char_spacing ?? 0),
                    ],
                ];
            })
            ->all();

        return [
            'page' => [
                'size' => $plantilla->tamano_hoja ?? 'a4',
                'orientation' => $plantilla->orientacion ?? 'horizontal',
                'canvas_width' => $this->resolveCanvasDimensions($plantilla->tamano_hoja ?? 'a4', $plantilla->orientacion ?? 'horizontal')['width'],
                'canvas_height' => $this->resolveCanvasDimensions($plantilla->tamano_hoja ?? 'a4', $plantilla->orientacion ?? 'horizontal')['height'],
            ],
            'elements' => $elements,
        ];
    }

    private function resolveEditorPage(array $structure, object $plantilla): array
    {
        $page = $structure['page'] ?? [];
        $size = $page['size'] ?? $plantilla->tamano_hoja ?? 'a4';
        $orientation = $page['orientation'] ?? $plantilla->orientacion ?? 'horizontal';
        $dimensions = $this->resolveCanvasDimensions($size, $orientation);

        return [
            'size' => $size,
            'orientation' => $orientation,
            'canvas_width' => (int) ($page['canvas_width'] ?? $dimensions['width']),
            'canvas_height' => (int) ($page['canvas_height'] ?? $dimensions['height']),
        ];
    }

    private function mapElementToFieldRecord(int $plantillaId, array $element, int $index): array
    {
        $style = $element['style'] ?? [];
        $type = (string) ($element['type'] ?? 'texto');

        return [
            'certificado_plantilla_id' => $plantillaId,
            'tipo' => $type,
            'nombre_campo' => $this->resolveFieldName($type, $element, $index),
            'texto_default' => $element['text'] ?? null,
            'valor_dinamico' => $element['field'] ?? null,
            'pos_x' => round((float) ($element['left'] ?? 0), 2),
            'pos_y' => round((float) ($element['top'] ?? 0), 2),
            'ancho' => round((float) ($element['width'] ?? 0), 2),
            'alto' => round((float) ($element['height'] ?? 0), 2),
            'font_family' => $style['fontFamily'] ?? null,
            'font_size' => isset($style['fontSize']) ? round((float) $style['fontSize'], 2) : null,
            'font_weight' => $style['fontWeight'] ?? null,
            'font_style' => $style['fontStyle'] ?? null,
            'text_align' => $style['textAlign'] ?? null,
            'color' => $style['fill'] ?? null,
            'line_height' => isset($style['lineHeight']) ? round((float) $style['lineHeight'], 2) : null,
            'char_spacing' => isset($style['charSpacing']) ? (int) $style['charSpacing'] : null,
            'orden' => $index + 1,
            'metadata' => json_encode([
                'underline' => (bool) ($style['underline'] ?? false),
                'image_src' => $element['image_src'] ?? null,
                'qr_value' => $element['qr_value'] ?? '',
                'qr_style' => $this->normalizeQrStyle($element['qr_style'] ?? []),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'updated_at' => now(),
        ];
    }

    private function resolveFieldName(string $type, array $element, int $index): string
    {
        if ($type === 'campo_dinamico' && !empty($element['field'])) {
            return (string) $element['field'];
        }

        return $type . '_' . ($index + 1);
    }

    private function hydrateElement(array $element, array $variables): array
    {
        if (($element['type'] ?? '') === 'campo_dinamico') {
            $field = $element['field'] ?? '';
            $element['text'] = $variables[$field] ?? '';
        }

        return $element;
    }

    private function resolveCanvasDimensions(string $size, string $orientation): array
    {
        $dimensions = match ($size) {
            'carta' => ['width' => 1056, 'height' => 816],
            default => ['width' => 1123, 'height' => 794],
        };

        return $orientation === 'vertical'
            ? ['width' => $dimensions['height'], 'height' => $dimensions['width']]
            : $dimensions;
    }

    private function resolvePaperName(string $size): string
    {
        return $size === 'carta' ? 'letter' : 'a4';
    }

    private function resolvePaperOrientation(string $orientation): string
    {
        return $orientation === 'vertical' ? 'portrait' : 'landscape';
    }

    private function resolvePaperDimensions(string $size, string $orientation): array
    {
        $dimensions = match ($this->resolvePaperName($size) . '-' . $this->resolvePaperOrientation($orientation)) {
            'a4-landscape' => ['width' => 841.89, 'height' => 595.28],
            'a4-portrait' => ['width' => 595.28, 'height' => 841.89],
            'letter-landscape' => ['width' => 792.00, 'height' => 612.00],
            'letter-portrait' => ['width' => 612.00, 'height' => 792.00],
            default => ['width' => 841.89, 'height' => 595.28],
        };

        return [0, 0, $dimensions['width'], $dimensions['height']];
    }

    private function formatHoursForCertificate(string $hours): string
    {
        $parts = explode(':', $hours);

        if (count($parts) >= 2) {
            return sprintf('%02d:%02d', (int) $parts[0], (int) $parts[1]);
        }

        return $hours;
    }

    private function getCertificateUser(int $userId): User
    {
        return User::query()
            ->select([
                'id',
                'name',
                'cod_estudiante',
            ])
            ->findOrFail($userId);
    }

    private function resolveUserHoursForCertificate(int $userId): string
    {
        $summary = $this->controlHorasService->getSummaryForUser($userId);

        return $this->formatHoursForCertificate($summary['horas_validadas'] ?? '00:00:00');
    }

    private function generateCertificateHash(): string
    {
        do {
            $hash = hash('sha256', Str::uuid() . '|' . microtime(true) . '|' . Str::random(16));
        } while (
            DB::table('certificado_emitidos')
                ->where('codigo_hash', $hash)
                ->exists()
        );

        return $hash;
    }

    private function generateQrImageDataUri(string $value, array $style = []): ?string
    {
        if ($value === '') {
            return null;
        }

        $style = $this->normalizeQrStyle($style);

        $options = new CertificadoQrOptions([
            'version' => 7,
            'outputType' => QROutputInterface::CUSTOM,
            'outputInterface' => CertificadoQrSvgOutput::class,
            'outputBase64' => true,
            'svgAddXmlHeader' => false,
            'svgUseFillAttributes' => true,
            'imageTransparent' => false,
            'drawLightModules' => true,
            'drawCircularModules' => $style['pattern'] === 'round',
            'circleRadius' => 0.45,
            'scale' => $style['scale'],
            'quietzoneSize' => $style['margin'],
            'bgColor' => $style['background'],
            'moduleValues' => [
                QRMatrix::M_FINDER_DARK => $style['foreground'],
                QRMatrix::M_FINDER_DOT => $style['eye'],
                QRMatrix::M_ALIGNMENT_DARK => $style['foreground'],
                QRMatrix::M_TIMING_DARK => $style['foreground'],
                QRMatrix::M_DATA_DARK => $style['foreground'],
                QRMatrix::M_DATA => $style['background'],
            ],
            'keepAsSquare' => [
                QRMatrix::M_FINDER_DARK,
                QRMatrix::M_FINDER_DOT,
            ],
            'cornerFrameShape' => $style['corner_frame_shape'],
            'cornerDotShape' => $style['corner_dot_shape'],
            'cornerTopLeft' => $style['corner_top_left'],
            'cornerTopRight' => $style['corner_top_right'],
            'cornerBottomLeft' => $style['corner_bottom_left'],
        ]);

        return (new QRCode($options))->render($value);
    }

    private function getDefaultQrStyle(): array
    {
        return [
            'foreground' => '#111827',
            'background' => '#ffffff',
            'eye' => '#0e7490',
            'pattern' => 'round',
            'corner_frame_shape' => 'rounded',
            'corner_dot_shape' => 'circle',
            'corner_top_left' => true,
            'corner_top_right' => true,
            'corner_bottom_left' => true,
            'margin' => 2,
            'scale' => 12,
        ];
    }

    private function normalizeQrStyle(array $style): array
    {
        $defaults = $this->getDefaultQrStyle();

        return [
            'foreground' => $this->normalizeHexColor($style['foreground'] ?? $defaults['foreground'], $defaults['foreground']),
            'background' => $this->normalizeHexColor($style['background'] ?? $defaults['background'], $defaults['background']),
            'eye' => $this->normalizeHexColor($style['eye'] ?? $defaults['eye'], $defaults['eye']),
            'pattern' => in_array(($style['pattern'] ?? $defaults['pattern']), ['round', 'square'], true)
                ? ($style['pattern'] ?? $defaults['pattern'])
                : $defaults['pattern'],
            'corner_frame_shape' => in_array(($style['corner_frame_shape'] ?? $defaults['corner_frame_shape']), ['none', 'square', 'rounded', 'circle'], true)
                ? ($style['corner_frame_shape'] ?? $defaults['corner_frame_shape'])
                : $defaults['corner_frame_shape'],
            'corner_dot_shape' => in_array(($style['corner_dot_shape'] ?? $defaults['corner_dot_shape']), ['none', 'square', 'circle'], true)
                ? ($style['corner_dot_shape'] ?? $defaults['corner_dot_shape'])
                : $defaults['corner_dot_shape'],
            'corner_top_left' => (bool) ($style['corner_top_left'] ?? $defaults['corner_top_left']),
            'corner_top_right' => (bool) ($style['corner_top_right'] ?? $defaults['corner_top_right']),
            'corner_bottom_left' => (bool) ($style['corner_bottom_left'] ?? $defaults['corner_bottom_left']),
            'margin' => max(0, min(10, (int) ($style['margin'] ?? $defaults['margin']))),
            'scale' => max(4, min(20, (int) ($style['scale'] ?? $defaults['scale']))),
        ];
    }

    private function normalizeHexColor(mixed $value, string $fallback): string
    {
        $value = is_string($value) ? trim($value) : '';

        if (preg_match('/^#([A-Fa-f0-9]{6})$/', $value) === 1) {
            return strtoupper($value);
        }

        return $fallback;
    }

}
