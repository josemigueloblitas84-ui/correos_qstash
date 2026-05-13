@extends('plantilla.app')

@section('title', 'Editar Plantilla de Certificado')

@section('content')
    <div class="app-content-header py-3">
        <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="mb-1">Editar plantilla de certificado</h2>
                <small class="text-muted">{{ $archivo }}</small>
            </div>

            <a href="{{ route('certificados.index') }}" class="btn btn-outline-secondary">
                Volver
            </a>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row g-4">
                <div class="col-xl-4">
                    <div class="card shadow-sm certificado-editor-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Campos editables</h5>
                            <span class="badge text-bg-light">Dinamico</span>
                        </div>

                        <div class="card-body">
                            <div class="certificado-toolbar mb-4">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnAgregarTexto">
                                    <i class="fas fa-plus me-1"></i> Texto
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm" id="btnAgregarFirma">
                                    <i class="fas fa-plus me-1"></i> Firma
                                </button>
                                <button type="button" class="btn btn-outline-dark btn-sm" id="btnAgregarQr">
                                    <i class="fas fa-plus me-1"></i> QR
                                </button>
                            </div>

                            <div class="certificado-toolbar-note mb-4">
                                Puedes agregar hasta 5 textos, 5 firmas y 1 QR.
                            </div>

                            <form id="certificadoEditorForm" autocomplete="off">
                                <div class="certificado-section mb-4">
                                    <div class="certificado-section__header">
                                        <h6 class="mb-0">Textos</h6>
                                        <span class="badge text-bg-primary" id="contadorTextos">0 / 5</span>
                                    </div>
                                    <div id="listaTextos" class="certificado-field-list"></div>
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

                                <div class="certificado-editor-help">
                                    <div class="small fw-semibold mb-2">Firma recomendada</div>
                                    <p class="mb-0 small text-muted">
                                        Para mejor resultado, usa firma dibujada o imagen PNG/WEBP con transparencia.
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="card shadow-sm certificado-preview-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Vista previa de la plantilla</h5>
                            <span class="badge text-bg-light">Maqueta inicial</span>
                        </div>

                        <div class="card-body">
                            <div class="certificado-preview-shell">
                                <div class="certificado-preview-stage" id="certificadoPreviewStage">
                                    <iframe
                                        src="{{ $pdfUrl }}"
                                        class="certificado-preview-frame"
                                        title="Vista previa PDF"
                                    ></iframe>

                                    <div id="certificadoDynamicMarkers"></div>
                                </div>
                            </div>

                            <div class="certificado-preview-note mt-3">
                                Los bloques son visuales por ahora. Aun no se guardan al recargar.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/certificados.css') }}">
@endpush

@push('scripts')
    <script>
        window.certificadoEditorConfig = {
            maxTextos: 5,
            maxFirmas: 5,
            maxQr: 1,
            defaultTextoLabel: 'Texto editable',
            defaultFirmaLabel: 'Firma',
            defaultQrLabel: 'QR / Verificacion'
        };
    </script>
    <script src="{{ asset('assets/js/certificados.js') }}"></script>
@endpush
