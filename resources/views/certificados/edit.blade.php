@extends('plantilla.app')

@section('title', 'Editar Plantilla de Certificado')

@section('content')
    <div class="app-content-header py-3">
        <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="mb-1">Constructor de certificado</h2>
                <small class="text-muted">{{ $archivo }}</small>
            </div>

            <a href="{{ route('certificados.index') }}" class="btn btn-outline-secondary">
                Volver
            </a>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            @include('mensajes')

            <div class="row g-4 align-items-start">
                <div class="col-xl-4">
                    <div class="card shadow-sm certificado-editor-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Elementos del certificado</h5>
                            <span class="badge text-bg-light">Canvas</span>
                        </div>

                        <div class="card-body">
                            <div class="certificado-toolbar mb-4">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnAgregarTexto">
                                    <i class="fas fa-plus me-1"></i> Texto
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" id="btnAgregarCampoDinamico">
                                    <i class="fas fa-plus me-1"></i> Campo dinamico
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm" id="btnAgregarFirma">
                                    <i class="fas fa-plus me-1"></i> Firma
                                </button>
                                <button type="button" class="btn btn-outline-dark btn-sm" id="btnAgregarQr">
                                    <i class="fas fa-plus me-1"></i> QR
                                </button>
                                <button type="button" class="btn btn-outline-warning btn-sm" id="btnAgregarImagen">
                                    <i class="fas fa-plus me-1"></i> Imagen
                                </button>
                            </div>

                            <div class="certificado-toolbar-note mb-4">
                                El PDF ya no se edita directamente. Aqui armas el certificado en un lienzo en blanco usando el PDF solo como referencia.
                            </div>

                            <div class="certificado-page-controls mb-4">
                                <div>
                                    <label for="tamanoHoja" class="form-label small mb-1">Tamaño de hoja</label>
                                    <select id="tamanoHoja" class="form-select form-select-sm">
                                        <option value="a4" selected>A4</option>
                                        <option value="carta">Carta</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="orientacionHoja" class="form-label small mb-1">Orientacion</label>
                                    <select id="orientacionHoja" class="form-select form-select-sm">
                                        <option value="horizontal" selected>Horizontal</option>
                                        <option value="vertical">Vertical</option>
                                    </select>
                                </div>
                            </div>

                            <form id="certificadoEditorForm" autocomplete="off">
                                <div class="certificado-section mb-4">
                                <div class="certificado-section__header">
                                    <h6 class="mb-0">Textos</h6>
                                    <span class="badge text-bg-primary" id="contadorTextos">0</span>
                                </div>
                                    <div id="listaTextos" class="certificado-field-list"></div>
                                </div>

                                <div class="certificado-section mb-4">
                                    <div class="certificado-section__header">
                                        <h6 class="mb-0">Campos dinamicos</h6>
                                        <span class="badge text-bg-info" id="contadorCamposDinamicos">0 / 10</span>
                                    </div>
                                    <div id="listaCamposDinamicos" class="certificado-field-list"></div>
                                </div>

                                <div class="certificado-section mb-4">
                                    <div class="certificado-section__header">
                                        <h6 class="mb-0">Firmas</h6>
                                        <span class="badge text-bg-success" id="contadorFirmas">0 / 5</span>
                                    </div>
                                    <div id="listaFirmas" class="certificado-field-list"></div>
                                </div>

                                <div class="certificado-section mb-4">
                                    <div class="certificado-section__header">
                                        <h6 class="mb-0">QR</h6>
                                        <span class="badge text-bg-dark" id="contadorQr">0 / 1</span>
                                    </div>
                                    <div id="listaQr" class="certificado-field-list"></div>
                                </div>

                                <div class="certificado-section mb-4">
                                    <div class="certificado-section__header">
                                        <h6 class="mb-0">Imagenes</h6>
                                        <span class="badge text-bg-warning" id="contadorImagenes">0</span>
                                    </div>
                                    <div id="listaImagenes" class="certificado-field-list"></div>
                                </div>

                                <div class="certificado-editor-help">
                                    <div class="small fw-semibold mb-2">Carga local</div>
                                    <p class="mb-0 small text-muted">
                                        Puedes subir imagenes y firmas directamente desde cualquier ubicacion de tu equipo.
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="card shadow-sm certificado-preview-card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Lienzo del certificado</h5>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary btn-sm" id="btnGuardarEstructura">
                                    Guardar estructura
                                </button>

                                <button type="button" class="btn btn-outline-danger btn-sm" id="btnDescargarCertificado">
                                    Vista previa PDF
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="certificado-builder-shell">
                                <div class="certificado-builder-stage" id="certificadoBuilderStage">
                                    <canvas id="certificadoFabricCanvas"></canvas>
                                </div>
                            </div>

                            <div class="certificado-preview-note mt-3">
                                Este lienzo es el certificado real que estas construyendo.
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm certificado-reference-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">PDF de referencia</h5>
                            <span class="badge text-bg-secondary">Solo guia</span>
                        </div>

                        <div class="card-body">
                            <div class="certificado-reference-shell">
                                <iframe
                                    src="{{ $pdfUrl }}#toolbar=0&navpanes=0&scrollbar=0&view=FitH"
                                    class="certificado-reference-frame"
                                    title="PDF de referencia"
                                ></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600;700&family=Poppins:wght@400;500;600;700&family=Roboto+Slab:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/certificados.css') }}">
@endpush

@push('scripts')
    <script>
        window.certificadoEditorConfig = {
            maxTextos: null,
            maxCamposDinamicos: 10,
            maxFirmas: 5,
            maxQr: 1,
            maxImagenes: null,
            pageSize: @json($editorPage['size']),
            pageOrientation: @json($editorPage['orientation']),
            canvasWidth: @json($editorPage['canvas_width']),
            canvasHeight: @json($editorPage['canvas_height']),
            initialStructure: @json($initialStructure),
            saveStructureUrl: @json($saveStructureUrl),
            previewCertificateUrl: @json($previewCertificateUrl),
            qrPreviewUrl: @json($qrPreviewUrl),
            csrfToken: @json(csrf_token()),
            defaultTextoLabel: 'Texto editable',
            defaultCampoDinamicoLabel: 'nombre_completo',
            defaultFirmaLabel: 'Firma',
            defaultQrLabel: 'QR / Verificacion',
            defaultImagenLabel: 'Imagen',
            dynamicFieldOptions: [
                { value: 'nombre_completo', label: 'Nombre completo', token: '@{{ nombre_completo }}' },
                { value: 'ci', label: 'CI', token: '@{{ ci }}' },
                { value: 'horas_institucionales', label: 'Horas institucionales', token: '@{{ horas_institucionales }}' }
            ]
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script src="{{ asset('assets/js/certificados.js') }}"></script>
@endpush
