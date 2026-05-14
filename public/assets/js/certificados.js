document.addEventListener('DOMContentLoaded', function () {
    const config = window.certificadoEditorConfig || {};
    const stage = document.getElementById('certificadoBuilderStage');
    const canvasElement = document.getElementById('certificadoFabricCanvas');
    const pageSizeSelect = document.getElementById('tamanoHoja');
    const pageOrientationSelect = document.getElementById('orientacionHoja');
    const btnGuardarEstructura = document.getElementById('btnGuardarEstructura');

    if (!stage || !canvasElement || typeof fabric === 'undefined') {
        return;
    }

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

    const pageSize = config.pageSize || 'a4';
    const pageOrientation = config.pageOrientation || 'horizontal';
    const initialDimensions = resolveCanvasDimensions(pageSize, pageOrientation);
    let canvasWidth = initialDimensions.width;
    let canvasHeight = initialDimensions.height;

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
            [TYPES.texto]: Number(config.maxTextos || 5),
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

    function createItem(type) {
        const definition = typeDefinitions[type];

        if (!definition || !canCreate(type)) {
            return;
        }

        const index = ++state.sequence[type];
        const id = definition.baseId + '_' + index;

        registerItem({
            id,
            type,
            object: definition.createObject(id, index),
            control: definition.createControl(id, index)
        });
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

        fabricCanvas.setWidth(canvasWidth);
        fabricCanvas.setHeight(canvasHeight);
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
            body: `<input type="text" class="form-control form-control-sm" id="input_${id}" value="${config.defaultQrLabel || 'QR / Verificacion'}">`
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
                            <option value="Times New Roman">Times New Roman</option>
                            <option value="Georgia">Georgia</option>
                            <option value="Verdana">Verdana</option>
                            <option value="Tahoma">Tahoma</option>
                            <option value="Courier New">Courier New</option>
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
        return new fabric.Group([
            new fabric.Rect({
                left: 0,
                top: 26,
                width: 170,
                height: 170,
                fill: 'transparent',
                stroke: 'rgba(15, 23, 42, 0.75)',
                strokeWidth: 2,
                rx: 12,
                ry: 12,
                selectable: false,
                evented: false
            }),
            buildReadonlyText('QR', {
                left: 10,
                top: 4,
                fontSize: 14,
                fill: '#111111',
                fontWeight: '700'
            }),
            new fabric.Textbox(config.defaultQrLabel || 'QR / Verificacion', {
                left: 20,
                top: 86,
                width: 130,
                fontSize: 15,
                fill: '#111111',
                textAlign: 'center',
                selectable: false,
                evented: false
            })
        ], buildGroupOptions({
            left: 130 + index * 18,
            top: 210 + index * 18,
            width: 190,
            height: 220,
            borderColor: '#111827',
            cornerColor: '#111827',
            id,
            data: { id, type: TYPES.qr, qr_value: config.defaultQrLabel || 'QR / Verificacion' }
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
        bindControlSelection(id, 'select, button');
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
            replaceSignatureGroup(id, canvas.toDataURL('image/png'));
        });

        uploadInput?.addEventListener('change', function (event) {
            const [file] = event.target.files || [];

            if (!file) {
                return;
            }

            if (!['image/png', 'image/webp'].includes(file.type)) {
                event.target.value = '';
                alert('Solo se permite PNG o WEBP para la firma.');
                return;
            }

            readFileAsDataURL(file, function (src) {
                replaceSignatureGroup(id, src);
            });
        });

        bindControlSelection(id, 'input, button, canvas');
    }

    function bindQrControl(id) {
        const input = document.getElementById('input_' + id);

        input?.addEventListener('input', function () {
            const item = state.items.get(id);

            if (!item) {
                return;
            }

            item.object.item(2).set('text', input.value.trim() || config.defaultQrLabel || 'QR / Verificacion');
            item.object.set('data', {
                ...(item.object.data || {}),
                qr_value: input.value.trim() || ''
            });
            fabricCanvas.requestRenderAll();
        });

        bindControlSelection(id);
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
                alert('Solo se permite PNG, JPG, WEBP o SVG para imagenes.');
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

    function replaceSignatureGroup(id, src) {
        replaceGroupWithImage({
            id,
            src,
            labelText: 'Firma',
            labelColor: '#16a34a',
            imageLeft: 12,
            imageTop: 34,
            imageMaxWidth: 220,
            imageMaxHeight: 100
        });
    }

    function replaceImageObject(id, src) {
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
                scaleX: snapshot.scaleX,
                scaleY: snapshot.scaleY,
                angle: snapshot.angle,
                borderColor: '#f59e0b',
                cornerColor: '#f59e0b',
                cornerStyle: 'circle',
                transparentCorners: false,
                padding: 10,
                id,
                data: { id, type: TYPES.imagen, mode: 'image' }
            });

            image.scaleToWidth(260);
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
                scaleX: snapshot.scaleX,
                scaleY: snapshot.scaleY,
                angle: snapshot.angle,
                borderColor: '#f59e0b',
                cornerColor: '#f59e0b',
                cornerStyle: 'circle',
                transparentCorners: false,
                padding: 10,
                id,
                data: { id, type: TYPES.imagen, mode: 'svg' }
            });

            if (svgObject.width && svgObject.width > 260) {
                svgObject.scaleToWidth(260);
            }

            replaceObject(id, currentObject, svgObject);
        });
    }

    function replaceGroupWithImage({ id, src, labelText, labelColor, imageLeft, imageTop, imageMaxWidth, imageMaxHeight }) {
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
                    source: src
                }
            }));

            replaceObject(id, currentObject, nextGroup);
        }, { crossOrigin: 'anonymous' });
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

    async function saveStructure() {
        if (!config.saveStructureUrl || !config.csrfToken) {
            alert('No se configuro la ruta para guardar la estructura.');
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

            alert(result.message || 'Estructura guardada correctamente.');
        } catch (error) {
            alert(error.message || 'Ocurrio un error al guardar la estructura.');
        }
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
                type,
                left: roundNumber(object.left),
                top: roundNumber(object.top),
                width: roundNumber(object.getScaledWidth()),
                height: roundNumber(object.getScaledHeight()),
                qr_value: object?.data?.qr_value || ''
            };
        }

        if (type === TYPES.imagen) {
            return {
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

    updateCounters();
});
