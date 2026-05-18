@extends('plantilla.app')

@section('title', 'Descargar Certificado')

@section('content')
    <div class="app-content-header py-3">
        <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="mb-1">Descargar certificado</h2>
                <small class="text-muted">Estamos preparando tu PDF con la emision registrada.</small>
            </div>

            <a href="{{ $backUrl }}" class="btn btn-outline-secondary">
                Volver
            </a>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-9">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body p-4 p-lg-5 text-center">
                            <div class="mb-3">
                                <span class="badge text-bg-warning px-3 py-2">Descarga autenticada</span>
                            </div>

                            <h3 class="mb-2">Tu PDF se esta preparando</h3>
                            <p class="text-muted mb-4" id="downloadState">
                                Esta descarga usara una de tus 5 oportunidades disponibles para este certificado.
                            </p>

                            <div class="mt-4 d-none" id="downloadPreviewWrap">
                                <img
                                    id="downloadPreviewImage"
                                    src=""
                                    alt="Vista previa del certificado"
                                    class="img-fluid border rounded-3"
                                    style="background: #fff;">
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
@endpush

@push('scripts')
    <script>
        window.certificadoDownloadIssuedConfig = {
            payloadUrl: @json($payloadUrl)
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const config = window.certificadoDownloadIssuedConfig || {};
            const stateLabel = document.getElementById('downloadState');
            const previewWrap = document.getElementById('downloadPreviewWrap');
            const previewImage = document.getElementById('downloadPreviewImage');

            if (!config.payloadUrl || !window.fabric || !window.jspdf?.jsPDF) {
                showError('No se pudo iniciar la descarga del certificado.');
                return;
            }

            startDownload();

            async function startDownload() {
                try {
                    updateState('Consultando la emision registrada...');

                    const response = await fetch(config.payloadUrl, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const payload = await response.json();

                    if (!response.ok) {
                        throw new Error(payload.message || 'No se pudo preparar el certificado para descargar.');
                    }

                    updateState('Renderizando el PDF de tu emision...');
                    const rendered = await renderPdfBlob(payload);
                    previewImage.src = rendered.previewImage;
                    previewWrap.classList.remove('d-none');
                    saveBlob(rendered.blob, payload.downloadFileName || 'certificado.pdf');
                    updateState('Tu certificado ya fue descargado.');
                } catch (error) {
                    showError(error.message || 'Ocurrio un error al descargar tu certificado.');
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

            function saveBlob(blob, fileName) {
                const objectUrl = URL.createObjectURL(blob);
                const link = document.createElement('a');

                link.href = objectUrl;
                link.download = fileName || 'certificado.pdf';
                document.body.appendChild(link);
                link.click();
                link.remove();
                URL.revokeObjectURL(objectUrl);
            }

            function updateState(message) {
                if (stateLabel) {
                    stateLabel.textContent = message;
                }
            }

            function showError(message) {
                updateState(message);

                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'error',
                        title: 'No se pudo descargar el certificado',
                        text: message
                    });
                    return;
                }

                window.alert(message);
            }
        });
    </script>
@endpush
