@extends('plantilla.app')

@section('title', 'Generar Certificado')

@section('content')
    <div class="app-content-header py-3">
        <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="mb-1">Generar certificado</h2>
                <small class="text-muted">{{ $archivo }}</small>
            </div>

            <a href="{{ $backUrl }}" class="btn btn-outline-secondary">
                Volver al dashboard
            </a>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-xl-7 col-lg-8">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body p-4 p-lg-5 text-center">
                            <div class="mb-3">
                                <span class="badge text-bg-primary px-3 py-2">Emision personal</span>
                            </div>

                            <h3 class="mb-2">Generar certificado</h3>
                            <p class="text-muted mb-4" id="estadoCertificado">
                                Al generar tu certificado, el sistema guardara la emision y te mostrara un QR de descarga unica.
                            </p>

                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <button type="button" class="btn btn-primary" id="btnGenerarCertificado">
                                    Emitir certificado
                                </button>
                                <a href="{{ $backUrl }}" class="btn btn-outline-secondary">
                                    Cancelar
                                </a>
                            </div>

                            <div class="mt-4 d-none" id="certificadoQrResult">
                                <div class="border rounded-4 p-4 bg-light-subtle">
                                    <h5 class="mb-2">QR de verificacion</h5>
                                    <p class="text-muted mb-3">
                                        Tu certificado ya fue emitido. Escanea este QR para verificar publicamente que el certificado fue emitido por el sistema.
                                    </p>

                                    <div class="d-flex justify-content-center mb-3">
                                        <img
                                            id="certificadoQrImage"
                                            src=""
                                            alt="QR de descarga del certificado"
                                            style="max-width: 240px; width: 100%; height: auto;">
                                    </div>

                                    <small class="text-muted d-block mt-3" id="certificadoQrUrl"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-9 col-lg-10 mt-4 d-none" id="certificadoPreviewWrap">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Vista previa del certificado emitido</h5>
                        </div>
                        <div class="card-body text-center">
                            <img
                                id="certificadoPreviewImage"
                                src=""
                                alt="Vista previa del certificado emitido"
                                class="img-fluid border rounded-3"
                                style="background: #fff;">
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
@endpush

@push('scripts')
    <script>
        window.certificadoGenerateConfig = {
            prepareUrl: @json($prepareCertificateUrl),
            csrfToken: @json(csrf_token())
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const config = window.certificadoGenerateConfig || {};
            const button = document.getElementById('btnGenerarCertificado');
            const stateLabel = document.getElementById('estadoCertificado');
            const qrResult = document.getElementById('certificadoQrResult');
            const qrImage = document.getElementById('certificadoQrImage');
            const qrUrl = document.getElementById('certificadoQrUrl');
            const previewWrap = document.getElementById('certificadoPreviewWrap');
            const previewImage = document.getElementById('certificadoPreviewImage');
            let isLoading = false;

            button?.addEventListener('click', generateCertificate);

            async function generateCertificate() {
                if (isLoading) {
                    return;
                }

                if (!config.prepareUrl || !window.fabric || !window.jspdf?.jsPDF) {
                    showError('No se pudo iniciar la generacion del certificado.');
                    return;
                }

                isLoading = true;
                setLoadingState(true);

                try {
                    updateState('Preparando tus datos y la estructura del certificado...');

                    const response = await fetch(config.prepareUrl, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const payload = await response.json();

                    if (!response.ok) {
                        throw new Error(payload.message || 'No se pudo preparar el certificado.');
                    }

                    updateState('Renderizando el certificado final...');
                    const renderedCertificate = await renderPdfBlob(payload);
                    const pdfBlob = renderedCertificate.blob;

                    if (!payload.emissionId || !payload.storeIssuedPdfUrl) {
                        throw new Error('La plantilla necesita un bloque QR para emitir el certificado.');
                    }

                    updateState('Guardando la copia emitida del certificado...');
                    await uploadIssuedPdf(payload.storeIssuedPdfUrl, pdfBlob, payload.downloadFileName);

                    showQrResult(payload, renderedCertificate.previewImage);
                    updateState('Tu certificado fue emitido correctamente. Ahora puedes revisar la vista previa y volver a la tabla para descargar el PDF hasta 5 veces.');
                    showSuccess('Tu certificado fue emitido correctamente.');
                } catch (error) {
                    showError(error.message || 'Ocurrio un error al generar tu certificado.');
                } finally {
                    isLoading = false;
                    setLoadingState(false);
                }
            }

            async function renderPdfBlob(payload) {
                const elements = Array.isArray(payload.elements) ? payload.elements : [];
                const page = payload.page || {};
                const canvasWidth = Number(page.canvas_width || 1123);
                const canvasHeight = Number(page.canvas_height || 794);

                await waitForFonts(elements);

                const hiddenCanvas = document.createElement('canvas');
                const renderCanvas = new fabric.StaticCanvas(hiddenCanvas, {
                    width: canvasWidth,
                    height: canvasHeight,
                    backgroundColor: '#ffffff',
                    renderOnAddRemove: false
                });

                for (const element of elements) {
                    const object = await buildObject(element);

                    if (object) {
                        renderCanvas.add(object);
                    }
                }

                renderCanvas.renderAll();

                const paper = Array.isArray(payload.paper_dimensions) ? payload.paper_dimensions : [0, 0, 841.89, 595.28];
                const pageWidth = Number(paper[2] || 841.89);
                const pageHeight = Number(paper[3] || 595.28);
                const orientation = pageWidth > pageHeight ? 'landscape' : 'portrait';

                const pdf = new window.jspdf.jsPDF({
                    orientation,
                    unit: 'pt',
                    format: [pageWidth, pageHeight],
                    compress: true
                });

                const imageData = renderCanvas.toDataURL({
                    format: 'png',
                    multiplier: 2,
                    enableRetinaScaling: true
                });

                pdf.addImage(imageData, 'PNG', 0, 0, pageWidth, pageHeight, undefined, 'FAST');
                renderCanvas.dispose();

                return {
                    blob: pdf.output('blob'),
                    previewImage: imageData
                };
            }

            async function buildObject(element) {
                const type = element?.type || '';
                const left = Number(element.left || 0);
                const top = Number(element.top || 0);
                const width = Number(element.width || 0);
                const height = Number(element.height || 0);
                const style = element.style || {};

                if (type === 'texto' || type === 'campo_dinamico') {
                    return new fabric.Textbox(element.text || '', {
                        left,
                        top,
                        width,
                        fontSize: Number(style.fontSize || 24),
                        fontFamily: style.fontFamily || 'Arial',
                        fill: style.fill || '#111111',
                        fontWeight: style.fontWeight || 'normal',
                        fontStyle: style.fontStyle || 'normal',
                        underline: !!style.underline,
                        textAlign: style.textAlign || 'left',
                        lineHeight: Number(style.lineHeight || 1.16),
                        charSpacing: Number(style.charSpacing || 0),
                        selectable: false,
                        evented: false,
                        editable: false
                    });
                }

                if ((type === 'imagen' || type === 'firma' || type === 'qr') && element.image_src) {
                    const image = await loadRenderable(element.image_src);

                    image.set({
                        left,
                        top,
                        selectable: false,
                        evented: false
                    });

                    scaleTo(image, width, height);
                    return image;
                }

                return null;
            }

            function scaleTo(object, targetWidth, targetHeight) {
                const baseWidth = Number(object.width || 1);
                const baseHeight = Number(object.height || 1);

                object.set({
                    scaleX: baseWidth > 0 ? targetWidth / baseWidth : 1,
                    scaleY: baseHeight > 0 ? targetHeight / baseHeight : 1
                });
            }

            async function loadRenderable(src) {
                return isSvgSource(src)
                    ? loadSvgAsImage(src)
                    : loadImage(src);
            }

            function loadImage(src) {
                return new Promise(function (resolve, reject) {
                    fabric.Image.fromURL(src, function (image) {
                        if (!image) {
                            reject(new Error('No se pudo cargar una imagen del certificado.'));
                            return;
                        }

                        resolve(image);
                    }, { crossOrigin: 'anonymous' });
                });
            }

            function loadSvgAsImage(src) {
                return rasterizeSvgSource(src).then(loadImage);
            }

            function isSvgSource(src) {
                return typeof src === 'string' && src.startsWith('data:image/svg+xml');
            }

            function decodeSvgSource(src) {
                const base64Marker = 'base64,';
                const markerIndex = src.indexOf(base64Marker);

                if (markerIndex !== -1) {
                    return atob(src.slice(markerIndex + base64Marker.length));
                }

                const commaIndex = src.indexOf(',');
                return decodeURIComponent(src.slice(commaIndex + 1));
            }

            function rasterizeSvgSource(src) {
                return new Promise(function (resolve, reject) {
                    const svgMarkup = decodeSvgSource(src);
                    const svgBlob = new Blob([svgMarkup], { type: 'image/svg+xml;charset=utf-8' });
                    const objectUrl = URL.createObjectURL(svgBlob);
                    const image = new Image();

                    image.onload = function () {
                        const width = image.naturalWidth || 600;
                        const height = image.naturalHeight || 600;
                        const canvas = document.createElement('canvas');
                        const context = canvas.getContext('2d');

                        canvas.width = width;
                        canvas.height = height;
                        context.drawImage(image, 0, 0, width, height);
                        URL.revokeObjectURL(objectUrl);
                        resolve(canvas.toDataURL('image/png'));
                    };

                    image.onerror = function () {
                        URL.revokeObjectURL(objectUrl);
                        reject(new Error('No se pudo rasterizar el QR SVG.'));
                    };

                    image.src = objectUrl;
                });
            }

            async function waitForFonts(elements) {
                if (!document.fonts) {
                    return;
                }

                const fontFamilies = Array.from(new Set(
                    (elements || [])
                        .map(function (element) {
                            return element?.style?.fontFamily || null;
                        })
                        .filter(Boolean)
                ));

                if (document.fonts.ready) {
                    await document.fonts.ready;
                }

                if (fontFamilies.length === 0) {
                    return;
                }

                await Promise.all(fontFamilies.map(function (fontFamily) {
                    return document.fonts.load(`400 24px "${fontFamily}"`);
                }));
            }

            async function uploadIssuedPdf(url, pdfBlob, downloadFileName) {
                const formData = new FormData();
                formData.append('archivo_pdf', new File([pdfBlob], downloadFileName || 'certificado.pdf', { type: 'application/pdf' }));
                formData.append('download_file_name', downloadFileName || 'certificado.pdf');

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': config.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'No se pudo guardar el certificado emitido.');
                }
            }

            function showQrResult(payload, certificatePreviewImage) {
                const qrElement = Array.isArray(payload.elements)
                    ? payload.elements.find(function (element) {
                        return element?.type === 'qr' && !!element.image_src;
                    })
                    : null;

                if (qrImage && qrElement?.image_src) {
                    qrImage.src = qrElement.image_src;
                }

                if (qrUrl) {
                    qrUrl.textContent = payload.verificationUrl || '';
                }

                if (previewImage && certificatePreviewImage) {
                    previewImage.src = certificatePreviewImage;
                }

                qrResult?.classList.remove('d-none');
                previewWrap?.classList.remove('d-none');
            }

            function updateState(message) {
                if (stateLabel) {
                    stateLabel.textContent = message;
                }
            }

            function setLoadingState(loading) {
                if (!button) {
                    return;
                }

                button.disabled = loading;
                button.textContent = loading ? 'Emitiendo...' : 'Emitir certificado';
            }

            function showSuccess(message) {
                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'success',
                        title: 'Certificado generado',
                        text: message
                    });
                    return;
                }

                window.alert(message);
            }

            function showError(message) {
                updateState(message);

                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'error',
                        title: 'No se pudo generar el certificado',
                        text: message
                    });
                    return;
                }

                window.alert(message);
            }
        });
    </script>
@endpush
