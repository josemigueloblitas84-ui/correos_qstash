document.addEventListener('DOMContentLoaded', function () {
    const config = window.certificadoEditorConfig || {};
    const stage = document.getElementById('certificadoBuilderStage');
    const canvasElement = document.getElementById('certificadoFabricCanvas');
    const pageSizeSelect = document.getElementById('tamanoHoja');
    const pageOrientationSelect = document.getElementById('orientacionHoja');
    const btnGuardarEstructura = document.getElementById('btnGuardarEstructura');
    const btnDescargarCertificado = document.getElementById('btnDescargarCertificado');

    if (!stage || !canvasElement || typeof fabric === 'undefined') {
        return;
    }

    patchCanvasTextBaseline();

    const TYPES = {
        texto: 'texto',
        campoDinamico: 'campo_dinamico',
        firma: 'firma',
        qr: 'qr',
        imagen: 'imagen'
    };

    const PAGE_DIMENSIONS = {
        a4: { width: 1123, height: 794 },
        carta: { width: 1056, height: 816 }
    };

    const SIGNATURE_LAYOUT = {
        groupWidth: 260,
        groupHeight: 160,
        imageLeft: 12,
        imageTop: 34,
        imageMaxWidth: 220,
        imageMaxHeight: 100
    };

    const DEFAULT_QR_STYLE = {
        foreground: '#111827',
        background: '#ffffff',
        eye: '#0e7490',
        pattern: 'round',
        corner_frame_shape: 'rounded',
        corner_dot_shape: 'circle',
        corner_top_left: true,
        corner_top_right: true,
        corner_bottom_left: true,
        margin: 2,
        scale: 12
    };

    const pageSize = config.pageSize || 'a4';
    const pageOrientation = config.pageOrientation || 'horizontal';
    const initialDimensions = resolveCanvasDimensions(pageSize, pageOrientation);
    let canvasWidth = Number(config.canvasWidth || initialDimensions.width);
    let canvasHeight = Number(config.canvasHeight || initialDimensions.height);

    const selectors = {
        buttons: {
            [TYPES.texto]: document.getElementById('btnAgregarTexto'),
            [TYPES.campoDinamico]: document.getElementById('btnAgregarCampoDinamico'),
            [TYPES.firma]: document.getElementById('btnAgregarFirma'),
            [TYPES.qr]: document.getElementById('btnAgregarQr'),
            [TYPES.imagen]: document.getElementById('btnAgregarImagen')
        },
        lists: {
            [TYPES.texto]: document.getElementById('listaTextos'),
            [TYPES.campoDinamico]: document.getElementById('listaCamposDinamicos'),
            [TYPES.firma]: document.getElementById('listaFirmas'),
            [TYPES.qr]: document.getElementById('listaQr'),
            [TYPES.imagen]: document.getElementById('listaImagenes')
        },
        counters: {
            [TYPES.texto]: document.getElementById('contadorTextos'),
            [TYPES.campoDinamico]: document.getElementById('contadorCamposDinamicos'),
            [TYPES.firma]: document.getElementById('contadorFirmas'),
            [TYPES.qr]: document.getElementById('contadorQr'),
            [TYPES.imagen]: document.getElementById('contadorImagenes')
        },
        max: {
            [TYPES.texto]: config.maxTextos === null ? null : Number(config.maxTextos || 5),
            [TYPES.campoDinamico]: Number(config.maxCamposDinamicos || 10),
            [TYPES.firma]: Number(config.maxFirmas || 5),
            [TYPES.qr]: Number(config.maxQr || 1),
            [TYPES.imagen]: config.maxImagenes ?? null
        }
    };

    const fabricCanvas = new fabric.Canvas('certificadoFabricCanvas', {
        width: canvasWidth,
        height: canvasHeight,
        backgroundColor: '#ffffff',
        preserveObjectStacking: true,
        selection: true
    });

    const state = {
        items: new Map(),
        qrPreviewTimers: new Map(),
        sequence: Object.values(TYPES).reduce(function (accumulator, type) {
            accumulator[type] = 0;
            return accumulator;
        }, {})
    };

    const typeDefinitions = {
        [TYPES.texto]: {
            baseId: 'texto',
            title: 'Texto',
            createObject: createTextObject,
            createControl: createTextControl,
            bindControl: bindTextControl
        },
        [TYPES.campoDinamico]: {
            baseId: 'campo_dinamico',
            title: 'Campo dinamico',
            createObject: createDynamicFieldObject,
            createControl: createDynamicFieldControl,
            bindControl: bindDynamicFieldControl
        },
        [TYPES.firma]: {
            baseId: 'firma',
            title: 'Firma',
            createObject: createSignaturePlaceholder,
            createControl: createFirmaControl,
            bindControl: bindFirmaControl
        },
        [TYPES.qr]: {
            baseId: 'qr',
            title: 'QR',
            createObject: createQrObject,
            createControl: createQrControl,
            bindControl: bindQrControl
        },
        [TYPES.imagen]: {
            baseId: 'imagen',
            title: 'Imagen',
            createObject: createImagePlaceholder,
            createControl: createImageControl,
            bindControl: bindImageControl
        }
    };

    fitCanvasToContainer();
    window.addEventListener('resize', fitCanvasToContainer);

    pageSizeSelect && (pageSizeSelect.value = pageSize);
    pageOrientationSelect && (pageOrientationSelect.value = pageOrientation);

    pageSizeSelect?.addEventListener('change', updateCanvasDimensions);
    pageOrientationSelect?.addEventListener('change', updateCanvasDimensions);
    btnGuardarEstructura?.addEventListener('click', saveStructure);
    btnDescargarCertificado?.addEventListener('click', previewCertificate);

    Object.entries(selectors.buttons).forEach(function ([type, button]) {
        button?.addEventListener('click', function () {
            createItem(type);
        });
    });

    Object.values(selectors.lists).forEach(function (listElement) {
        listElement?.addEventListener('click', function (event) {
            const removeButton = event.target.closest('[data-remove-marker]');

            if (removeButton) {
                removeItem(removeButton.getAttribute('data-remove-marker'));
            }
        });
    });

    fabricCanvas.on('selection:created', syncActiveState);
    fabricCanvas.on('selection:updated', syncActiveState);
    fabricCanvas.on('selection:cleared', syncActiveState);
    fabricCanvas.on('object:moving', syncActiveState);

    document.addEventListener('keydown', handleCanvasKeyboardMove);

    loadInitialStructure();

    function createItem(type, savedElement = null) {
        const definition = typeDefinitions[type];

        if (!definition || !canCreate(type)) {
            return;
        }

        const index = ++state.sequence[type];
        const id = definition.baseId + '_' + index;
        const item = {
            id,
            type,
            object: definition.createObject(id, index),
            control: definition.createControl(id, index)
        };

        registerItem(item);

        if (savedElement) {
            hydrateItemFromSavedElement(item, savedElement);
        }
    }

    function canCreate(type) {
        const max = selectors.max[type];
        return max === null || getCountByType(type) < max;
    }

    function fitCanvasToContainer() {
        const containerWidth = stage.clientWidth;
        const scale = containerWidth / canvasWidth;
        const containerHeight = canvasHeight * scale;

        canvasElement.style.width = containerWidth + 'px';
        canvasElement.style.height = containerHeight + 'px';
        stage.style.height = containerHeight + 'px';

        fabricCanvas.setZoom(scale);
        fabricCanvas.setDimensions({
            width: containerWidth,
            height: containerHeight
        });
        fabricCanvas.calcOffset();
        fabricCanvas.requestRenderAll();
    }

    function updateCanvasDimensions() {
        const nextSize = pageSizeSelect?.value || 'a4';
        const nextOrientation = pageOrientationSelect?.value || 'horizontal';
        const dimensions = resolveCanvasDimensions(nextSize, nextOrientation);

        canvasWidth = dimensions.width;
        canvasHeight = dimensions.height;

        fabricCanvas.setDimensions({
            width: canvasWidth,
            height: canvasHeight
        });
        fitCanvasToContainer();
    }

    function registerItem(item) {
        state.items.set(item.id, item);
        appendControl(item);
        fabricCanvas.add(item.object);
        fabricCanvas.setActiveObject(item.object);
        updateCounters();
        syncActiveState();
        fabricCanvas.requestRenderAll();
    }

    function appendControl(item) {
        const list = selectors.lists[item.type];
        const definition = typeDefinitions[item.type];

        list?.appendChild(item.control);
        definition?.bindControl(item.id);
    }

    function createTextControl(id, index) {
        return createSimpleControl({
            id,
            title: 'Texto ' + index,
            body: `
                <textarea class="form-control form-control-sm" id="input_${id}" rows="3">${config.defaultTextoLabel || 'Texto editable'}</textarea>
                ${buildTextStyleControls(id)}
            `
        });
    }

    function createDynamicFieldControl(id, index) {
        const options = (config.dynamicFieldOptions || []).map(function (option) {
            return `<option value="${option.value}">${option.label}</option>`;
        }).join('');

        return createSimpleControl({
            id,
            title: 'Campo dinamico ' + index,
            body: `
                <label class="form-label small mb-1">Dato a mostrar</label>
                <select class="form-select form-select-sm" id="input_${id}">
                    ${options}
                </select>
                ${buildTextStyleControls(id)}
                <div class="form-text mt-2">Este bloque se llenara automaticamente al seleccionar usuarios.</div>
            `
        });
    }

    function createFirmaControl(id, index) {
        return createSimpleControl({
            id,
            title: 'Firma ' + index,
            body: `
                <div class="certificado-sign-mode">
                    <button type="button" class="btn btn-outline-success btn-sm active" data-sign-mode="${id}" data-mode="draw">
                        Dibujar
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-sign-mode="${id}" data-mode="upload">
                        Subir PNG/WEBP
                    </button>
                </div>

                <div class="certificado-sign-panel" id="panel_draw_${id}">
                    <div class="certificado-sign-canvas-wrap">
                        <canvas class="certificado-sign-canvas" id="canvas_${id}" width="520" height="180"></canvas>
                    </div>

                    <div class="d-flex gap-2 mt-2">
                        <button type="button" class="btn btn-sm btn-outline-danger" id="clear_${id}">Limpiar</button>
                        <button type="button" class="btn btn-sm btn-success" id="apply_${id}">Aplicar firma</button>
                    </div>
                </div>

                <div class="certificado-sign-panel d-none" id="panel_upload_${id}">
                    <input type="file" class="form-control form-control-sm" id="input_${id}" accept="image/png,image/webp">
                    <div class="form-text">Solo PNG o WEBP transparentes.</div>
                </div>
            `
        });
    }

    function createQrControl(id) {
        return createSimpleControl({
            id,
            title: 'QR',
            body: `
                <label class="form-label small mb-1">Texto de referencia</label>
                <input type="text" class="form-control form-control-sm mb-3" id="input_${id}" value="${config.defaultQrLabel || 'QR / Verificacion'}">

                <div class="row g-2">
                    <div class="col-4">
                        <label class="form-label small mb-1">Color QR</label>
                        <input type="color" class="form-control form-control-color w-100" data-qr-style="${id}" data-qr-key="foreground" value="${DEFAULT_QR_STYLE.foreground}">
                    </div>
                    <div class="col-4">
                        <label class="form-label small mb-1">Fondo</label>
                        <input type="color" class="form-control form-control-color w-100" data-qr-style="${id}" data-qr-key="background" value="${DEFAULT_QR_STYLE.background}">
                    </div>
                    <div class="col-4">
                        <label class="form-label small mb-1">Ojos</label>
                        <input type="color" class="form-control form-control-color w-100" data-qr-style="${id}" data-qr-key="eye" value="${DEFAULT_QR_STYLE.eye}">
                    </div>
                    <div class="col-6">
                        <label class="form-label small mb-1">Patron</label>
                        <select class="form-select form-select-sm" data-qr-style="${id}" data-qr-key="pattern">
                            <option value="round">Circular</option>
                            <option value="square">Cuadrado</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small mb-1">Marco esquinas</label>
                        <select class="form-select form-select-sm" data-qr-style="${id}" data-qr-key="corner_frame_shape">
                            <option value="rounded">Redondeado</option>
                            <option value="square">Cuadrado</option>
                            <option value="circle">Circular</option>
                            <option value="none">Sin marco</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small mb-1">Punto esquinas</label>
                        <select class="form-select form-select-sm" data-qr-style="${id}" data-qr-key="corner_dot_shape">
                            <option value="circle">Circular</option>
                            <option value="square">Cuadrado</option>
                            <option value="none">Sin punto</option>
                        </select>
                    </div>
                    <div class="col-3">
                        <label class="form-label small mb-1">Margen</label>
                        <input type="number" min="0" max="10" class="form-control form-control-sm" data-qr-style="${id}" data-qr-key="margin" value="${DEFAULT_QR_STYLE.margin}">
                    </div>
                    <div class="col-3">
                        <label class="form-label small mb-1">Densidad</label>
                        <input type="number" min="4" max="20" class="form-control form-control-sm" data-qr-style="${id}" data-qr-key="scale" value="${DEFAULT_QR_STYLE.scale}">
                    </div>
                    <div class="col-12">
                        <label class="form-label small mb-1">Esquinas visibles</label>
                        <div class="d-flex flex-wrap gap-3 pt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" data-qr-style="${id}" data-qr-key="corner_top_left" checked>
                                <label class="form-check-label small">Superior izquierda</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" data-qr-style="${id}" data-qr-key="corner_top_right" checked>
                                <label class="form-check-label small">Superior derecha</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" data-qr-style="${id}" data-qr-key="corner_bottom_left" checked>
                                <label class="form-check-label small">Inferior izquierda</label>
                            </div>
                        </div>
                        <div class="form-text">Quitar esquinas puede volver ilegible el QR.</div>
                    </div>
                </div>
            `
        });
    }

    function createImageControl(id, index) {
        return createSimpleControl({
            id,
            title: 'Imagen ' + index,
            body: `
                <input type="file" class="form-control form-control-sm" id="input_${id}" accept="image/png,image/jpeg,image/webp,image/svg+xml,.svg">
                <div class="form-text mt-2">Puedes seleccionar PNG, JPG, WEBP o SVG desde cualquier carpeta de tu equipo.</div>
            `
        });
    }

    function createSimpleControl({ id, title, body }) {
        const wrapper = document.createElement('div');
        wrapper.className = 'certificado-field-card';
        wrapper.id = 'control_' + id;
        wrapper.innerHTML = `
            <div class="certificado-field-card__header">
                <strong>${title}</strong>
                <button type="button" class="btn btn-sm btn-link text-danger p-0" data-remove-marker="${id}">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            ${body}
        `;
        return wrapper;
    }

    function buildTextStyleControls(id) {
        return `
            <div class="certificado-text-tools mt-2">
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small mb-1">Fuente</label>
                        <select class="form-select form-select-sm" data-text-font-family="${id}">
                            <option value="Arial">Arial</option>
                            <option value="Montserrat">Montserrat</option>
                            <option value="Poppins">Poppins</option>
                            <option value="Lato">Lato</option>
                            <option value="Open Sans">Open Sans</option>
                            <option value="Roboto Slab">Roboto Slab</option>
                            <option value="Times New Roman">Times New Roman</option>
                            <option value="Georgia">Georgia</option>
                            <option value="Verdana">Verdana</option>
                            <option value="Tahoma">Tahoma</option>
                            <option value="Courier New">Courier New</option>
                            <option value="Trebuchet MS">Trebuchet MS</option>
                            <option value="Garamond">Garamond</option>
                        </select>
                    </div>
                    <div class="col-3">
                        <label class="form-label small mb-1">Tam.</label>
                        <input type="number" min="8" max="120" value="24" class="form-control form-control-sm" data-text-font-size="${id}">
                    </div>
                    <div class="col-3">
                        <label class="form-label small mb-1">Color</label>
                        <input type="color" value="#111111" class="form-control form-control-color w-100" data-text-color="${id}">
                    </div>
                    <div class="col-12">
                        <div class="btn-group btn-group-sm w-100" role="group" aria-label="Estilo de texto">
                            <button type="button" class="btn btn-outline-secondary" data-text-style="${id}" data-style="bold">N</button>
                            <button type="button" class="btn btn-outline-secondary" data-text-style="${id}" data-style="italic">K</button>
                            <button type="button" class="btn btn-outline-secondary" data-text-style="${id}" data-style="underline">S</button>
                            <button type="button" class="btn btn-outline-secondary active" data-text-align="${id}" data-align="left">Izq</button>
                            <button type="button" class="btn btn-outline-secondary" data-text-align="${id}" data-align="center">Centro</button>
                            <button type="button" class="btn btn-outline-secondary" data-text-align="${id}" data-align="right">Der</button>
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="form-label small mb-1">Interlineado</label>
                        <input type="range" min="0.8" max="2.4" step="0.1" value="1.16" class="form-range" data-text-line-height="${id}">
                    </div>
                    <div class="col-6">
                        <label class="form-label small mb-1">Espaciado</label>
                        <input type="range" min="0" max="400" step="10" value="0" class="form-range" data-text-char-spacing="${id}">
                    </div>
                </div>
            </div>
        `;
    }

    function createTextObject(id, index) {
        return buildTextObject(config.defaultTextoLabel || 'Texto editable', {
            left: 70 + index * 18,
            top: 70 + index * 18,
            width: 280,
            fontSize: 24,
            fill: '#111111',
            fontFamily: 'Arial',
            textAlign: 'left',
            fontWeight: 'normal',
            fontStyle: 'normal',
            underline: false,
            lineHeight: 1.16,
            charSpacing: 0,
            borderColor: '#2563eb',
            cornerColor: '#2563eb',
            id,
            data: { id, type: TYPES.texto }
        });
    }

    function createDynamicFieldObject(id, index) {
        return buildTextObject(resolveDynamicFieldLabel(config.defaultCampoDinamicoLabel || 'nombre_completo'), {
            left: 85 + index * 18,
            top: 95 + index * 18,
            width: 340,
            fontSize: 24,
            fill: '#0369a1',
            fontFamily: 'Arial',
            textAlign: 'center',
            fontWeight: 'normal',
            fontStyle: 'normal',
            underline: false,
            lineHeight: 1.16,
            charSpacing: 0,
            backgroundColor: 'rgba(14, 165, 233, 0.10)',
            borderColor: '#0ea5e9',
            cornerColor: '#0ea5e9',
            id,
            data: {
                id,
                type: TYPES.campoDinamico,
                field: config.defaultCampoDinamicoLabel || 'nombre_completo'
            }
        });
    }

    function buildTextObject(text, options) {
        return new fabric.Textbox(text, {
            cornerStyle: 'circle',
            transparentCorners: false,
            padding: 10,
            hoverCursor: 'move',
            moveCursor: 'move',
            ...options
        });
    }

    function createSignaturePlaceholder(id, index) {
        return new fabric.Group([
            buildReadonlyText('Firma', {
                left: 12,
                top: 10,
                fontSize: 14,
                fill: '#16a34a',
                fontWeight: '700'
            }),
            buildReadonlyText(config.defaultFirmaLabel || 'Firma', {
                left: 12,
                top: 54,
                fontSize: 22,
                fill: '#111111'
            })
        ], buildGroupOptions({
            left: 90 + index * 18,
            top: 140 + index * 18,
            width: 260,
            height: 160,
            borderColor: '#16a34a',
            cornerColor: '#16a34a',
            id,
            data: { id, type: TYPES.firma, mode: 'placeholder', source: null }
        }));
    }

    function createQrObject(id, index) {
        const qrStyle = getDefaultQrStyle();

        return new fabric.Group([
            new fabric.Rect({
                left: 0,
                top: 26,
                width: 170,
                height: 170,
                fill: qrStyle.background,
                stroke: qrStyle.foreground,
                strokeWidth: 2,
                rx: qrStyle.pattern === 'round' ? 18 : 0,
                ry: qrStyle.pattern === 'round' ? 18 : 0,
                selectable: false,
                evented: false
            }),
            buildReadonlyText('QR', {
                left: 10,
                top: 4,
                fontSize: 14,
                fill: qrStyle.eye,
                fontWeight: '700'
            }),
            new fabric.Textbox(config.defaultQrLabel || 'QR / Verificacion', {
                left: 20,
                top: 86,
                width: 130,
                fontSize: 15,
                fill: qrStyle.foreground,
                textAlign: 'center',
                selectable: false,
                evented: false
            })
        ], buildGroupOptions({
            left: 130 + index * 18,
            top: 210 + index * 18,
            width: 190,
            height: 220,
            borderColor: qrStyle.foreground,
            cornerColor: qrStyle.foreground,
            id,
            data: {
                id,
                type: TYPES.qr,
                qr_value: config.defaultQrLabel || 'QR / Verificacion',
                qr_style: qrStyle
            }
        }));
    }

    function createImagePlaceholder(id, index) {
        return new fabric.Group([
            new fabric.Rect({
                left: 0,
                top: 0,
                width: 240,
                height: 160,
                fill: '#f8fafc',
                stroke: '#f59e0b',
                strokeDashArray: [8, 6],
                strokeWidth: 2,
                rx: 12,
                ry: 12,
                selectable: false,
                evented: false
            }),
            buildReadonlyText(config.defaultImagenLabel || 'Imagen', {
                left: 78,
                top: 64,
                fontSize: 24,
                fill: '#92400e',
                fontWeight: '700'
            })
        ], buildGroupOptions({
            left: 160 + index * 20,
            top: 120 + index * 20,
            width: 240,
            height: 160,
            borderColor: '#f59e0b',
            cornerColor: '#f59e0b',
            id,
            data: { id, type: TYPES.imagen, mode: 'placeholder' }
        }));
    }

    function buildReadonlyText(text, options) {
        return new fabric.Text(text, {
            selectable: false,
            evented: false,
            ...options
        });
    }

    function buildGroupOptions(options) {
        return {
            cornerStyle: 'circle',
            transparentCorners: false,
            padding: 10,
            hoverCursor: 'move',
            moveCursor: 'move',
            ...options
        };
    }

    function bindTextControl(id) {
        bindTextInput(id, config.defaultTextoLabel || 'Texto editable');
        bindTextStyleControls(id);
        bindControlSelection(id, 'textarea, input, button');
    }

    function bindDynamicFieldControl(id) {
        const input = document.getElementById('input_' + id);

        input?.addEventListener('change', function () {
            const item = state.items.get(id);

            if (!item) {
                return;
            }

            const field = input.value || 'nombre_completo';

            item.object.set('text', resolveDynamicFieldLabel(field));
            item.object.set('data', {
                ...(item.object.data || {}),
                field
            });
            fabricCanvas.requestRenderAll();
        });

        bindTextStyleControls(id);
        bindControlSelection(id, 'select, input, button');
    }

    function bindTextInput(id, fallbackText) {
        const input = document.getElementById('input_' + id);

        input?.addEventListener('input', function () {
            const item = state.items.get(id);

            if (!item) {
                return;
            }

            item.object.set('text', input.value || fallbackText);
            fabricCanvas.requestRenderAll();
        });
    }

    function bindTextStyleControls(id) {
        bindAlignButtons(id);
        bindStyleButtons(id);
        bindStyleInput(id, 'fontFamily', '[data-text-font-family="ID"]', 'change', function (input) {
            return input.value || 'Arial';
        });
        bindStyleInput(id, 'fontSize', '[data-text-font-size="ID"]', 'input', function (input) {
            return Number(input.value || 24);
        });
        bindStyleInput(id, 'fill', '[data-text-color="ID"]', 'input', function (input) {
            return input.value || '#111111';
        });
        bindStyleInput(id, 'lineHeight', '[data-text-line-height="ID"]', 'input', function (input) {
            return Number(input.value || 1.16);
        });
        bindStyleInput(id, 'charSpacing', '[data-text-char-spacing="ID"]', 'input', function (input) {
            return Number(input.value || 0);
        });
    }

    function bindAlignButtons(id) {
        const buttons = document.querySelectorAll('[data-text-align="' + id + '"]');

        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                const align = button.getAttribute('data-align') || 'left';

                updateTextObjectStyle(id, 'textAlign', align);
                setExclusiveButtonState(buttons, button);
            });
        });
    }

    function bindStyleButtons(id) {
        document.querySelectorAll('[data-text-style="' + id + '"]').forEach(function (button) {
            button.addEventListener('click', function () {
                const item = state.items.get(id);

                if (!item) {
                    return;
                }

                const style = button.getAttribute('data-style');
                const togglers = {
                    bold: ['fontWeight', 'bold', 'normal'],
                    italic: ['fontStyle', 'italic', 'normal'],
                    underline: ['underline', true, false]
                };

                if (!togglers[style]) {
                    return;
                }

                const [property, activeValue, inactiveValue] = togglers[style];
                const isActive = item.object[property] === activeValue;

                item.object.set(property, isActive ? inactiveValue : activeValue);
                toggleStyleButtonState(button, !isActive);
                fabricCanvas.requestRenderAll();
            });
        });
    }

    function bindStyleInput(id, property, selectorTemplate, eventName, resolver) {
        const selector = selectorTemplate.replace('ID', id);
        const input = document.querySelector(selector);

        input?.addEventListener(eventName, function () {
            updateTextObjectStyle(id, property, resolver(input));
        });
    }

    function updateTextObjectStyle(id, property, value) {
        const item = state.items.get(id);

        if (!item) {
            return;
        }

        item.object.set(property, value);
        fabricCanvas.requestRenderAll();
    }

    function setExclusiveButtonState(buttons, activeButton) {
        buttons.forEach(function (button) {
            const isActive = button === activeButton;
            button.classList.toggle('active', isActive);
            button.classList.toggle('btn-secondary', isActive);
            button.classList.toggle('btn-outline-secondary', !isActive);
        });
    }

    function toggleStyleButtonState(button, isActive) {
        button.classList.toggle('active', isActive);
        button.classList.toggle('btn-secondary', isActive);
        button.classList.toggle('btn-outline-secondary', !isActive);
    }

    function bindFirmaControl(id) {
        const uploadInput = document.getElementById('input_' + id);
        const canvas = document.getElementById('canvas_' + id);
        const clearButton = document.getElementById('clear_' + id);
        const applyButton = document.getElementById('apply_' + id);
        const drawPanel = document.getElementById('panel_draw_' + id);
        const uploadPanel = document.getElementById('panel_upload_' + id);
        const modeButtons = document.querySelectorAll('[data-sign-mode="' + id + '"]');

        if (canvas) {
            setupSignatureCanvas(canvas);
        }

        modeButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const mode = button.getAttribute('data-mode');

                modeButtons.forEach(function (item) {
                    item.classList.remove('active', 'btn-success');
                    item.classList.add('btn-outline-secondary');
                });

                drawPanel.classList.toggle('d-none', mode !== 'draw');
                uploadPanel.classList.toggle('d-none', mode === 'draw');
                button.classList.add('active', 'btn-success');
                button.classList.remove('btn-outline-secondary');
            });
        });

        clearButton?.addEventListener('click', function () {
            const context = canvas.getContext('2d');
            context.clearRect(0, 0, canvas.width, canvas.height);
            initSignatureCanvas(context, canvas);
        });

        applyButton?.addEventListener('click', function () {
            replaceSignatureImage(id, canvas.toDataURL('image/png'));
        });

        uploadInput?.addEventListener('change', function (event) {
            const [file] = event.target.files || [];

            if (!file) {
                return;
            }

            if (!['image/png', 'image/webp'].includes(file.type)) {
                event.target.value = '';
                showErrorMessage('Solo se permite PNG o WEBP para la firma.');
                return;
            }

            readFileAsDataURL(file, function (src) {
                replaceSignatureImage(id, src);
            });
        });

        bindControlSelection(id, 'input, button, canvas');
    }

    function bindQrControl(id) {
        const input = document.getElementById('input_' + id);
        const styleInputs = document.querySelectorAll('[data-qr-style="' + id + '"]');

        input?.addEventListener('input', function () {
            const item = state.items.get(id);

            if (!item) {
                return;
            }

            item.object.set('data', {
                ...(item.object.data || {}),
                qr_value: input.value.trim() || ''
            });
            syncQrObjectText(item.object, input.value.trim() || config.defaultQrLabel || 'QR / Verificacion');
            fabricCanvas.requestRenderAll();
            requestQrPreview(id);
        });

        styleInputs.forEach(function (control) {
            const eventName = control.tagName === 'SELECT' ? 'change' : 'input';

            control.addEventListener(eventName, function () {
                const item = state.items.get(id);

                if (!item) {
                    return;
                }

                const currentStyle = normalizeQrStyle(item.object?.data?.qr_style || {});
                const key = control.getAttribute('data-qr-key');
                let value = control.type === 'checkbox' ? control.checked : control.value;

                if (key === 'margin' || key === 'scale') {
                    value = Number(value || 0);
                }

                const nextStyle = normalizeQrStyle({
                    ...currentStyle,
                    [key]: value
                });

                item.object.set('data', {
                    ...(item.object.data || {}),
                    qr_style: nextStyle
                });

                applyQrPreviewStyle(item.object, nextStyle);
                fabricCanvas.requestRenderAll();
                requestQrPreview(id);
            });
        });

        bindControlSelection(id, 'input, select, button');
        requestQrPreview(id);
    }

    function bindImageControl(id) {
        const input = document.getElementById('input_' + id);

        input?.addEventListener('change', function (event) {
            const [file] = event.target.files || [];

            if (!file) {
                return;
            }

            if (!['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'].includes(file.type)) {
                event.target.value = '';
                showErrorMessage('Solo se permite PNG, JPG, WEBP o SVG para imagenes.');
                return;
            }

            if (file.type === 'image/svg+xml') {
                readFileAsText(file, function (svgText) {
                    replaceSvgObject(id, svgText);
                });
                return;
            }

            readFileAsDataURL(file, function (src) {
                replaceImageObject(id, src);
            });
        });

        bindControlSelection(id, 'input, button');
    }

    function bindControlSelection(id, blockedSelector = 'input, button') {
        document.getElementById('control_' + id)?.addEventListener('click', function (event) {
            if (event.target.closest(blockedSelector)) {
                return;
            }

            const item = state.items.get(id);

            if (!item) {
                return;
            }

            fabricCanvas.setActiveObject(item.object);
            fabricCanvas.requestRenderAll();
            syncActiveState();
        });
    }

    function replaceSignatureImage(id, src, savedElement = null) {
        const item = state.items.get(id);

        if (!item) {
            return;
        }

        const currentObject = item.object;
        const snapshot = getObjectSnapshot(currentObject);
        const targetWidth = Number(savedElement?.width || currentObject.getScaledWidth() || SIGNATURE_LAYOUT.imageMaxWidth);
        const targetHeight = Number(savedElement?.height || currentObject.getScaledHeight() || SIGNATURE_LAYOUT.imageMaxHeight);

        fabric.Image.fromURL(src, function (image) {
            image.set({
                left: snapshot.left,
                top: snapshot.top,
                angle: snapshot.angle,
                borderColor: '#16a34a',
                cornerColor: '#16a34a',
                cornerStyle: 'circle',
                transparentCorners: false,
                padding: 10,
                id,
                data: {
                    id,
                    type: TYPES.firma,
                    mode: 'image',
                    source: src,
                    db_id: currentObject?.data?.db_id || null
                }
            });

            scaleObjectToDimensions(image, targetWidth, targetHeight);

            replaceObject(id, currentObject, image);
        }, { crossOrigin: 'anonymous' });
    }

    function replaceImageObject(id, src, savedElement = null) {
        const item = state.items.get(id);

        if (!item) {
            return;
        }

        const currentObject = item.object;
        const snapshot = getObjectSnapshot(currentObject);

        fabric.Image.fromURL(src, function (image) {
            image.set({
                left: snapshot.left,
                top: snapshot.top,
                angle: snapshot.angle,
                borderColor: '#f59e0b',
                cornerColor: '#f59e0b',
                cornerStyle: 'circle',
                transparentCorners: false,
                padding: 10,
                id,
                data: {
                    id,
                    type: TYPES.imagen,
                    mode: 'image',
                    db_id: currentObject?.data?.db_id || null
                }
            });

            image.scaleToWidth(260);

            if (savedElement?.width && savedElement?.height) {
                scaleObjectToDimensions(image, Number(savedElement.width), Number(savedElement.height));
            } else {
                image.set({
                    scaleX: snapshot.scaleX,
                    scaleY: snapshot.scaleY
                });
            }

            replaceObject(id, currentObject, image);
        }, { crossOrigin: 'anonymous' });
    }

    function replaceQrPreviewImage(id, src, savedElement = null) {
        const item = state.items.get(id);

        if (!item || !src) {
            return;
        }

        const currentObject = item.object;
        const snapshot = getObjectSnapshot(currentObject);
        const targetWidth = Number(savedElement?.width || currentObject.getScaledWidth() || 190);
        const targetHeight = Number(savedElement?.height || currentObject.getScaledHeight() || 220);
        const qrData = {
            ...(currentObject.data || {}),
            id,
            type: TYPES.qr,
            mode: 'preview-image',
            preview_src: src
        };

        if (isSvgSource(src)) {
            rasterizeSvgSource(src)
                .then(function (pngDataUrl) {
                    replaceQrPreviewImage(id, pngDataUrl, savedElement);
                })
                .catch(function () {
                    // keep current preview if rasterization fails
                });
            return;
        }

        fabric.Image.fromURL(src, function (image) {
            image.set({
                left: snapshot.left,
                top: snapshot.top,
                angle: snapshot.angle,
                borderColor: qrData.qr_style?.foreground || '#111827',
                cornerColor: qrData.qr_style?.foreground || '#111827',
                cornerStyle: 'circle',
                transparentCorners: false,
                padding: 10,
                id,
                data: qrData
            });

            scaleObjectToDimensions(image, targetWidth, targetHeight);
            replaceObject(id, currentObject, image);
        }, { crossOrigin: 'anonymous' });
    }

    function replaceSvgObject(id, svgText) {
        const item = state.items.get(id);

        if (!item) {
            return;
        }

        const currentObject = item.object;
        const snapshot = getObjectSnapshot(currentObject);

        fabric.loadSVGFromString(svgText, function (objects, options) {
            const svgObject = fabric.util.groupSVGElements(objects, options);

            svgObject.set({
                left: snapshot.left,
                top: snapshot.top,
                angle: snapshot.angle,
                borderColor: '#f59e0b',
                cornerColor: '#f59e0b',
                cornerStyle: 'circle',
                transparentCorners: false,
                padding: 10,
                id,
                data: {
                    id,
                    type: TYPES.imagen,
                    mode: 'svg',
                    db_id: currentObject?.data?.db_id || null
                }
            });

            if (svgObject.width && svgObject.width > 260) {
                svgObject.scaleToWidth(260);
            }

            svgObject.set({
                scaleX: snapshot.scaleX,
                scaleY: snapshot.scaleY
            });

            replaceObject(id, currentObject, svgObject);
        });
    }

    function replaceGroupWithImage({ id, src, labelText, labelColor, imageLeft, imageTop, imageMaxWidth, imageMaxHeight, savedElement = null }) {
        const item = state.items.get(id);

        if (!item) {
            return;
        }

        const currentObject = item.object;
        const snapshot = getObjectSnapshot(currentObject);

        fabric.Image.fromURL(src, function (image) {
            image.set({
                left: imageLeft,
                top: imageTop,
                selectable: false,
                evented: false
            });

            image.scaleToWidth(imageMaxWidth);
            if (image.getScaledHeight() > imageMaxHeight) {
                image.scaleToHeight(imageMaxHeight);
            }

            const nextGroup = new fabric.Group([
                buildReadonlyText(labelText, {
                    left: 12,
                    top: 8,
                    fontSize: 14,
                    fill: labelColor,
                    fontWeight: '700'
                }),
                image
            ], buildGroupOptions({
                left: snapshot.left,
                top: snapshot.top,
                scaleX: snapshot.scaleX,
                scaleY: snapshot.scaleY,
                angle: snapshot.angle,
                width: currentObject.width,
                height: currentObject.height,
                borderColor: labelColor,
                cornerColor: labelColor,
                id,
                data: {
                    id,
                    type: item.type,
                    mode: 'image',
                    source: src,
                    signature_box: buildSignatureBoxData(image),
                    signature_absolute_box: null,
                    db_id: currentObject?.data?.db_id || null
                }
            }));

            if (savedElement?.width && savedElement?.height) {
                scaleObjectToDimensions(nextGroup, Number(savedElement.width), Number(savedElement.height));
            }

            replaceObject(id, currentObject, nextGroup);
        }, { crossOrigin: 'anonymous' });
    }

    function scaleObjectToDimensions(object, targetWidth, targetHeight) {
        const baseWidth = Number(object.width || 1);
        const baseHeight = Number(object.height || 1);

        object.set({
            scaleX: baseWidth > 0 ? targetWidth / baseWidth : 1,
            scaleY: baseHeight > 0 ? targetHeight / baseHeight : 1
        });
    }

    function replaceObject(id, currentObject, nextObject) {
        const item = state.items.get(id);

        fabricCanvas.remove(currentObject);
        fabricCanvas.add(nextObject);
        fabricCanvas.setActiveObject(nextObject);

        if (item) {
            item.object = nextObject;
        }

        fabricCanvas.requestRenderAll();
        syncActiveState();
    }

    function getObjectSnapshot(object) {
        return {
            left: object.left,
            top: object.top,
            scaleX: object.scaleX,
            scaleY: object.scaleY,
            angle: object.angle
        };
    }

    function readFileAsDataURL(file, callback) {
        const reader = new FileReader();
        reader.onload = function (event) {
            callback(event.target.result);
        };
        reader.readAsDataURL(file);
    }

    function readFileAsText(file, callback) {
        const reader = new FileReader();
        reader.onload = function (event) {
            callback(event.target.result);
        };
        reader.readAsText(file);
    }

    function setupSignatureCanvas(canvas) {
        const context = canvas.getContext('2d');
        let drawing = false;

        initSignatureCanvas(context, canvas);

        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDraw);
        canvas.addEventListener('mouseleave', stopDraw);
        canvas.addEventListener('touchstart', startDraw, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', stopDraw);

        function startDraw(event) {
            drawing = true;
            const point = getCanvasPoint(canvas, event);
            context.beginPath();
            context.moveTo(point.x, point.y);
            event.preventDefault();
        }

        function draw(event) {
            if (!drawing) {
                return;
            }

            const point = getCanvasPoint(canvas, event);
            context.lineTo(point.x, point.y);
            context.stroke();
            event.preventDefault();
        }

        function stopDraw() {
            drawing = false;
        }
    }

    function initSignatureCanvas(context, canvas) {
        context.clearRect(0, 0, canvas.width, canvas.height);
        context.strokeStyle = '#111111';
        context.lineWidth = 2.4;
        context.lineCap = 'round';
        context.lineJoin = 'round';
    }

    function getCanvasPoint(canvas, event) {
        const rect = canvas.getBoundingClientRect();
        const point = event.touches && event.touches.length ? event.touches[0] : event;

        return {
            x: (point.clientX - rect.left) * (canvas.width / rect.width),
            y: (point.clientY - rect.top) * (canvas.height / rect.height)
        };
    }

    function removeItem(id) {
        const item = state.items.get(id);

        if (!item) {
            return;
        }

        fabricCanvas.remove(item.object);
        item.control.remove();
        state.items.delete(id);
        updateCounters();
        syncActiveState();
        fabricCanvas.requestRenderAll();
    }

    function syncActiveState() {
        const activeObject = fabricCanvas.getActiveObject();

        state.items.forEach(function (item) {
            item.control.classList.toggle('is-active', item.object === activeObject);
        });
    }

    function updateCounters() {
        Object.entries(selectors.counters).forEach(function ([type, counter]) {
            if (!counter) {
                return;
            }

            const max = selectors.max[type];
            const count = getCountByType(type);

            counter.textContent = max === null ? String(count) : `${count} / ${max}`;
        });

        Object.entries(selectors.buttons).forEach(function ([type, button]) {
            if (!button) {
                return;
            }

            const max = selectors.max[type];
            button.disabled = max !== null && getCountByType(type) >= max;
        });
    }

    function getCountByType(type) {
        let count = 0;

        state.items.forEach(function (item) {
            if (item.type === type) {
                count += 1;
            }
        });

        return count;
    }

    function handleCanvasKeyboardMove(event) {
        const activeElement = document.activeElement;
        const isTyping = activeElement && (
            activeElement.tagName === 'INPUT' ||
            activeElement.tagName === 'TEXTAREA' ||
            activeElement.tagName === 'SELECT' ||
            activeElement.isContentEditable
        );

        if (isTyping) {
            return;
        }

        const activeObject = fabricCanvas.getActiveObject();

        if (!activeObject) {
            return;
        }

        const keyDirections = {
            ArrowLeft: { left: -1, top: 0 },
            ArrowRight: { left: 1, top: 0 },
            ArrowUp: { left: 0, top: -1 },
            ArrowDown: { left: 0, top: 1 }
        };

        const direction = keyDirections[event.key];

        if (!direction) {
            return;
        }

        const step = event.shiftKey ? 10 : 1;
        const nextLeft = Number(activeObject.left || 0) + (direction.left * step);
        const nextTop = Number(activeObject.top || 0) + (direction.top * step);

        activeObject.set({
            left: Math.max(0, nextLeft),
            top: Math.max(0, nextTop)
        });
        activeObject.setCoords();
        fabricCanvas.requestRenderAll();
        syncActiveState();
        event.preventDefault();
    }

    function resolveDynamicFieldLabel(field) {
        const option = (config.dynamicFieldOptions || []).find(function (item) {
            return item.value === field;
        });

        return option?.label || 'Nombre completo';
    }

    function resolveCanvasDimensions(size, orientation) {
        const base = PAGE_DIMENSIONS[size] || PAGE_DIMENSIONS.a4;
        const isVertical = orientation === 'vertical';

        return isVertical
            ? { width: base.height, height: base.width }
            : { width: base.width, height: base.height };
    }

    function loadInitialStructure() {
        const structure = config.initialStructure || {};
        const page = structure.page || {};
        const elements = Array.isArray(structure.elements) ? structure.elements : [];

        if (page.size && pageSizeSelect) {
            pageSizeSelect.value = page.size;
        }

        if (page.orientation && pageOrientationSelect) {
            pageOrientationSelect.value = page.orientation;
        }

        if (page.size || page.orientation) {
            updateCanvasDimensions();
        }

        if (page.canvas_width && page.canvas_height) {
            canvasWidth = Number(page.canvas_width);
            canvasHeight = Number(page.canvas_height);
            fabricCanvas.setDimensions({
                width: canvasWidth,
                height: canvasHeight
            });
            fitCanvasToContainer();
        }

        elements.forEach(function (element) {
            const type = normalizeType(element.type);

            if (type) {
                createItem(type, element);
            }
        });
    }

    function normalizeType(type) {
        const validTypes = Object.values(TYPES);
        return validTypes.includes(type) ? type : null;
    }

    function hydrateItemFromSavedElement(item, element) {
        if (!item?.object || !element) {
            return;
        }

        const object = item.object;
        const style = element.style || {};
        const dbId = Number(element.db_id || 0) || null;

        object.set('data', {
            ...(object.data || {}),
            db_id: dbId
        });

        if (item.type === TYPES.texto || item.type === TYPES.campoDinamico) {
            object.set({
                left: Number(element.left || 0),
                top: Number(element.top || 0),
                width: Number(element.width || object.width || 280),
                fontSize: Number(style.fontSize || object.fontSize || 24),
                fontFamily: style.fontFamily || object.fontFamily || 'Arial',
                fill: style.fill || object.fill || '#111111',
                fontWeight: style.fontWeight || object.fontWeight || 'normal',
                fontStyle: style.fontStyle || object.fontStyle || 'normal',
                underline: !!style.underline,
                textAlign: style.textAlign || object.textAlign || 'left',
                lineHeight: Number(style.lineHeight || object.lineHeight || 1.16),
                charSpacing: Number(style.charSpacing || object.charSpacing || 0),
                text: element.text || ''
            });

            if (item.type === TYPES.campoDinamico) {
                const field = element.field || config.defaultCampoDinamicoLabel || 'nombre_completo';

                object.set({
                    text: resolveDynamicFieldLabel(field),
                    data: {
                        ...(object.data || {}),
                        field,
                        db_id: dbId
                    }
                });

                setInputValue('#input_' + item.id, field);
            } else {
                setInputValue('#input_' + item.id, object.text || '');
            }

            object.scaleX = 1;
            object.scaleY = 1;
            syncTextStyleControlState(item.id, object);
            return;
        }

        object.set({
            left: Number(element.left || 0),
            top: Number(element.top || 0)
        });

        if (item.type === TYPES.firma) {
            if (element.image_src) {
                replaceSignatureImage(item.id, element.image_src, element);
            } else {
                applyGroupScale(object, element);
            }
            return;
        }

        if (item.type === TYPES.qr) {
            const qrValue = element.qr_value || '';
            const qrStyle = normalizeQrStyle(element.qr_style || {});
            object.set('data', {
                ...(object.data || {}),
                qr_value: qrValue,
                qr_style: qrStyle,
                db_id: dbId
            });
            syncQrObjectText(object, qrValue || config.defaultQrLabel || 'QR / Verificacion');
            applyQrPreviewStyle(object, qrStyle);
            applyGroupScale(object, element);
            syncQrControlState(item.id, qrValue, qrStyle);
            requestQrPreview(item.id, element);
            return;
        }

        if (item.type === TYPES.imagen) {
            if (element.image_src) {
                replaceImageObject(item.id, element.image_src, element);
            } else {
                applyGroupScale(object, element);
            }
        }
    }

    function applyGroupScale(object, element) {
        const baseWidth = Number(object.width || 1);
        const baseHeight = Number(object.height || 1);
        const targetWidth = Number(element.width || baseWidth);
        const targetHeight = Number(element.height || baseHeight);

        object.scaleX = baseWidth > 0 ? targetWidth / baseWidth : 1;
        object.scaleY = baseHeight > 0 ? targetHeight / baseHeight : 1;
        object.setCoords();
        fabricCanvas.requestRenderAll();
    }

    function syncTextStyleControlState(id, object) {
        setInputValue('[data-text-font-family="' + id + '"]', object.fontFamily || 'Arial');
        setInputValue('[data-text-font-size="' + id + '"]', object.fontSize || 24);
        setInputValue('[data-text-color="' + id + '"]', object.fill || '#111111');
        setInputValue('[data-text-line-height="' + id + '"]', object.lineHeight || 1.16);
        setInputValue('[data-text-char-spacing="' + id + '"]', object.charSpacing || 0);

        document.querySelectorAll('[data-text-align="' + id + '"]').forEach(function (button) {
            const isActive = button.getAttribute('data-align') === (object.textAlign || 'left');
            button.classList.toggle('active', isActive);
            button.classList.toggle('btn-secondary', isActive);
            button.classList.toggle('btn-outline-secondary', !isActive);
        });

        document.querySelectorAll('[data-text-style="' + id + '"]').forEach(function (button) {
            const style = button.getAttribute('data-style');
            const isActive = (
                (style === 'bold' && object.fontWeight === 'bold') ||
                (style === 'italic' && object.fontStyle === 'italic') ||
                (style === 'underline' && !!object.underline)
            );

            button.classList.toggle('active', isActive);
            button.classList.toggle('btn-secondary', isActive);
            button.classList.toggle('btn-outline-secondary', !isActive);
        });
    }

    function syncQrControlState(id, value, qrStyle = null) {
        setInputValue('#input_' + id, value || '');

        const style = normalizeQrStyle(qrStyle || {});

        document.querySelectorAll('[data-qr-style="' + id + '"]').forEach(function (input) {
            const key = input.getAttribute('data-qr-key');

            if (key && style[key] !== undefined) {
                if (input.type === 'checkbox') {
                    input.checked = !!style[key];
                } else {
                    input.value = style[key];
                }
            }
        });
    }

    function syncQrObjectText(object, text) {
        if (!object || object.type !== 'group' || typeof object.item !== 'function') {
            return;
        }

        const textObject = object.item(2);

        if (textObject) {
            textObject.set('text', text);
        }
    }

    function setInputValue(selector, value) {
        const input = document.querySelector(selector);

        if (input) {
            input.value = value;
        }
    }

    async function saveStructure() {
        if (!config.saveStructureUrl || !config.csrfToken) {
            showErrorMessage('No se configuro la ruta para guardar la estructura.');
            return;
        }

        try {
            const response = await fetch(config.saveStructureUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken
                },
                body: JSON.stringify(buildStructurePayload())
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'No se pudo guardar la estructura.');
            }

            showSuccessMessage(result.message || 'Estructura guardada correctamente.');
        } catch (error) {
            showErrorMessage(error.message || 'Ocurrio un error al guardar la estructura.');
        }
    }

    async function previewCertificate() {
        if (!config.previewCertificateUrl) {
            showErrorMessage('No se configuro la ruta para generar la vista previa del certificado.');
            return;
        }

        if (!window.jspdf?.jsPDF) {
            showErrorMessage('No se pudo cargar la libreria para generar el PDF.');
            return;
        }

        toggleDownloadButton(true);

        try {
            const response = await fetch(config.previewCertificateUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'No se pudo preparar la vista previa del certificado.');
            }

            await renderAndDownloadCertificate(result);
        } catch (error) {
            showErrorMessage(error.message || 'Ocurrio un error al generar la vista previa del certificado.');
        } finally {
            toggleDownloadButton(false);
        }
    }

    async function renderAndDownloadCertificate(payload) {
        const elements = Array.isArray(payload.elements) ? payload.elements : [];
        const page = payload.page || {};
        const canvasWidth = Number(page.canvas_width || 1123);
        const canvasHeight = Number(page.canvas_height || 794);

        await waitForDownloadFonts(elements);

        const hiddenCanvas = document.createElement('canvas');
        const renderCanvas = new fabric.StaticCanvas(hiddenCanvas, {
            width: canvasWidth,
            height: canvasHeight,
            backgroundColor: '#ffffff',
            renderOnAddRemove: false
        });

        for (const element of elements) {
            const object = await buildDownloadObject(element);

            if (object) {
                renderCanvas.add(object);
            }
        }

        renderCanvas.renderAll();
        exportCanvasAsPdf(renderCanvas, payload.paper_dimensions, payload.downloadFileName);
        renderCanvas.dispose();
    }

    async function waitForDownloadFonts(elements) {
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

    async function buildDownloadObject(element) {
        const type = element?.type || '';
        const left = Number(element.left || 0);
        const top = Number(element.top || 0);
        const width = Number(element.width || 0);
        const height = Number(element.height || 0);
        const style = element.style || {};

        if (type === TYPES.texto || type === TYPES.campoDinamico) {
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

        if ((type === TYPES.imagen || type === TYPES.firma) && element.image_src) {
            const image = await loadFabricRenderable(element.image_src);

            image.set({
                left,
                top,
                selectable: false,
                evented: false
            });

            scaleObjectToDimensions(image, width, height);
            return image;
        }

        if (type === TYPES.qr) {
            if (element.image_src) {
                const image = await loadFabricRenderable(element.image_src);

                image.set({
                    left,
                    top,
                    selectable: false,
                    evented: false
                });

                scaleObjectToDimensions(image, width, height);
                return image;
            }

            return new fabric.Rect({
                left,
                top,
                width,
                height,
                fill: 'transparent',
                stroke: '#111827',
                strokeWidth: 2,
                rx: 12,
                ry: 12,
                selectable: false,
                evented: false
            });
        }

        return null;
    }

    function loadFabricImage(src) {
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

    function loadFabricSvg(src) {
        return new Promise(function (resolve, reject) {
            rasterizeSvgSource(src)
                .then(function (pngDataUrl) {
                    return loadFabricImage(pngDataUrl);
                })
                .then(resolve)
                .catch(reject);
        });
    }

    async function loadFabricRenderable(src) {
        return isSvgSource(src)
            ? loadFabricSvg(src)
            : loadFabricImage(src);
    }

    function isSvgSource(src) {
        return typeof src === 'string' && src.startsWith('data:image/svg+xml');
    }

    function decodeSvgSource(src) {
        if (!isSvgSource(src)) {
            return null;
        }

        const commaIndex = src.indexOf(',');

        if (commaIndex === -1) {
            return null;
        }

        const meta = src.slice(0, commaIndex);
        const payload = src.slice(commaIndex + 1);

        try {
            return meta.includes(';base64')
                ? atob(payload)
                : decodeURIComponent(payload);
        } catch (error) {
            return null;
        }
    }

    function rasterizeSvgSource(src) {
        return new Promise(function (resolve, reject) {
            const image = new Image();

            image.onload = function () {
                const width = image.naturalWidth || image.width || 512;
                const height = image.naturalHeight || image.height || 512;
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');

                if (!context) {
                    reject(new Error('No se pudo preparar la previsualizacion del QR.'));
                    return;
                }

                canvas.width = width;
                canvas.height = height;
                context.clearRect(0, 0, width, height);
                context.drawImage(image, 0, 0, width, height);
                resolve(canvas.toDataURL('image/png'));
            };

            image.onerror = function () {
                reject(new Error('No se pudo rasterizar el SVG del QR.'));
            };

            image.src = src;
        });
    }

    function exportCanvasAsPdf(renderCanvas, paperDimensions, downloadFileName) {
        const { jsPDF } = window.jspdf;
        const paper = Array.isArray(paperDimensions) ? paperDimensions : [0, 0, 841.89, 595.28];
        const pageWidth = Number(paper[2] || 841.89);
        const pageHeight = Number(paper[3] || 595.28);
        const orientation = pageWidth > pageHeight ? 'landscape' : 'portrait';

        const pdf = new jsPDF({
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
        pdf.save(downloadFileName || 'certificado.pdf');
    }

    function toggleDownloadButton(isLoading) {
        if (!btnDescargarCertificado) {
            return;
        }

        btnDescargarCertificado.disabled = isLoading;
        btnDescargarCertificado.textContent = isLoading ? 'Generando...' : 'Vista previa PDF';
    }

    function buildStructurePayload() {
        return {
            page: {
                size: pageSizeSelect?.value || 'a4',
                orientation: pageOrientationSelect?.value || 'horizontal',
                canvas_width: canvasWidth,
                canvas_height: canvasHeight
            },
            elements: fabricCanvas.getObjects().map(exportCanvasObject).filter(Boolean)
        };
    }

    function exportCanvasObject(object) {
        const type = object?.data?.type || '';

        if (type === TYPES.texto || type === TYPES.campoDinamico) {
            return {
                db_id: object?.data?.db_id || null,
                type,
                left: roundNumber(object.left),
                top: roundNumber(object.top),
                width: roundNumber(object.getScaledWidth()),
                height: roundNumber(object.getScaledHeight()),
                text: object.text || '',
                field: object?.data?.field || null,
                style: {
                    fontSize: object.fontSize || 24,
                    fontFamily: object.fontFamily || 'Arial',
                    fill: object.fill || '#111111',
                    fontWeight: object.fontWeight || 'normal',
                    fontStyle: object.fontStyle || 'normal',
                    underline: !!object.underline,
                    textAlign: object.textAlign || 'left',
                    lineHeight: object.lineHeight || 1.16,
                    charSpacing: object.charSpacing || 0
                }
            };
        }

        if (type === TYPES.firma) {
            return {
                db_id: object?.data?.db_id || null,
                type,
                left: roundNumber(object.left),
                top: roundNumber(object.top),
                width: roundNumber(object.getScaledWidth()),
                height: roundNumber(object.getScaledHeight()),
                image_src: object?.data?.source || null
            };
        }

        if (type === TYPES.qr) {
            return {
                db_id: object?.data?.db_id || null,
                type,
                left: roundNumber(object.left),
                top: roundNumber(object.top),
                width: roundNumber(object.getScaledWidth()),
                height: roundNumber(object.getScaledHeight()),
                qr_value: object?.data?.qr_value || '',
                qr_style: normalizeQrStyle(object?.data?.qr_style || {})
            };
        }

        if (type === TYPES.imagen) {
            return {
                db_id: object?.data?.db_id || null,
                type,
                left: roundNumber(object.left),
                top: roundNumber(object.top),
                width: roundNumber(object.getScaledWidth()),
                height: roundNumber(object.getScaledHeight()),
                image_src: object.toDataURL({
                    format: 'png',
                    multiplier: 2
                })
            };
        }

        return null;
    }

    function roundNumber(value) {
        return Math.round((Number(value) || 0) * 100) / 100;
    }

    function patchCanvasTextBaseline() {
        const prototype = window.CanvasRenderingContext2D?.prototype;

        if (!prototype || prototype.__certificadoBaselinePatched) {
            return;
        }

        const descriptor = Object.getOwnPropertyDescriptor(prototype, 'textBaseline');

        if (!descriptor?.set || !descriptor?.get || descriptor.configurable === false) {
            return;
        }

        Object.defineProperty(prototype, 'textBaseline', {
            configurable: true,
            enumerable: descriptor.enumerable ?? false,
            get() {
                return descriptor.get.call(this);
            },
            set(value) {
                descriptor.set.call(this, value === 'alphabetical' ? 'alphabetic' : value);
            }
        });

        prototype.__certificadoBaselinePatched = true;
    }

    function showSuccessMessage(message) {
        showAlertMessage({
            icon: 'success',
            title: 'Correcto',
            text: message
        });
    }

    function showErrorMessage(message) {
        showAlertMessage({
            icon: 'error',
            title: 'Ocurrio un problema',
            text: message
        });
    }

    function showAlertMessage(options) {
        if (window.Swal?.fire) {
            window.Swal.fire({
                confirmButtonText: 'Aceptar',
                ...options
            });
            return;
        }

        window.alert(options.text || '');
    }

    function getDefaultQrStyle() {
        return { ...DEFAULT_QR_STYLE };
    }

    function normalizeQrStyle(style) {
        return {
            foreground: normalizeQrHex(style.foreground, DEFAULT_QR_STYLE.foreground),
            background: normalizeQrHex(style.background, DEFAULT_QR_STYLE.background),
            eye: normalizeQrHex(style.eye, DEFAULT_QR_STYLE.eye),
            pattern: style.pattern === 'square' ? 'square' : 'round',
            corner_frame_shape: ['none', 'square', 'rounded', 'circle'].includes(style.corner_frame_shape)
                ? style.corner_frame_shape
                : DEFAULT_QR_STYLE.corner_frame_shape,
            corner_dot_shape: ['none', 'square', 'circle'].includes(style.corner_dot_shape)
                ? style.corner_dot_shape
                : DEFAULT_QR_STYLE.corner_dot_shape,
            corner_top_left: style.corner_top_left !== false,
            corner_top_right: style.corner_top_right !== false,
            corner_bottom_left: style.corner_bottom_left !== false,
            margin: clampNumber(style.margin, DEFAULT_QR_STYLE.margin, 0, 10),
            scale: clampNumber(style.scale, DEFAULT_QR_STYLE.scale, 4, 20)
        };
    }

    function normalizeQrHex(value, fallback) {
        return /^#[0-9a-fA-F]{6}$/.test(String(value || '').trim())
            ? String(value).trim()
            : fallback;
    }

    function clampNumber(value, fallback, min, max) {
        const number = Number(value);

        if (Number.isNaN(number)) {
            return fallback;
        }

        return Math.max(min, Math.min(max, number));
    }

    function applyQrPreviewStyle(object, qrStyle) {
        if (!object) {
            return;
        }

        const style = normalizeQrStyle(qrStyle);

        if (object.type !== 'group') {
            object.set({
                borderColor: style.foreground,
                cornerColor: style.foreground
            });
            object.setCoords();
            return;
        }

        const frame = object.item(0);
        const label = object.item(1);
        const text = object.item(2);

        if (frame) {
            frame.set({
                fill: style.background,
                stroke: style.foreground,
                rx: style.pattern === 'round' ? 18 : 0,
                ry: style.pattern === 'round' ? 18 : 0
            });
        }

        if (label) {
            label.set('fill', style.eye);
        }

        if (text) {
            text.set('fill', style.foreground);
        }

        object.set({
            borderColor: style.foreground,
            cornerColor: style.foreground
        });
        object.setCoords();
    }

    function requestQrPreview(id, savedElement = null) {
        if (!config.qrPreviewUrl || !config.csrfToken) {
            return;
        }

        const existingTimer = state.qrPreviewTimers.get(id);

        if (existingTimer) {
            clearTimeout(existingTimer);
        }

        const timer = setTimeout(function () {
            performQrPreview(id, savedElement);
        }, 250);

        state.qrPreviewTimers.set(id, timer);
    }

    async function performQrPreview(id, savedElement = null) {
        const item = state.items.get(id);

        if (!item) {
            return;
        }

        const qrValue = item.object?.data?.qr_value || config.defaultQrLabel || 'QR / Verificacion';
        const qrStyle = normalizeQrStyle(item.object?.data?.qr_style || {});

        try {
            const response = await fetch(config.qrPreviewUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken
                },
                body: JSON.stringify({
                    qr_value: qrValue,
                    qr_style: qrStyle
                })
            });

            const result = await response.json();

            if (!response.ok || !result.image_src) {
                return;
            }

            replaceQrPreviewImage(id, result.image_src, savedElement);
        } catch (error) {
            // keep placeholder if preview endpoint fails
        }
    }

    updateCounters();
});
