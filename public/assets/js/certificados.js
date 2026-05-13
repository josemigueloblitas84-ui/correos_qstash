document.addEventListener('DOMContentLoaded', function () {
    const config = window.certificadoEditorConfig || {};
    const stage = document.getElementById('certificadoPreviewStage');
    const dynamicMarkers = document.getElementById('certificadoDynamicMarkers');

    const btnAgregarTexto = document.getElementById('btnAgregarTexto');
    const btnAgregarFirma = document.getElementById('btnAgregarFirma');
    const btnAgregarQr = document.getElementById('btnAgregarQr');

    const listaTextos = document.getElementById('listaTextos');
    const listaFirmas = document.getElementById('listaFirmas');
    const listaQr = document.getElementById('listaQr');

    const contadorTextos = document.getElementById('contadorTextos');
    const contadorFirmas = document.getElementById('contadorFirmas');
    const contadorQr = document.getElementById('contadorQr');

    if (!stage || !dynamicMarkers) {
        return;
    }

    let textCount = 0;
    let firmaCount = 0;
    let qrCount = 0;

    btnAgregarTexto?.addEventListener('click', function () {
        if (listaTextos.children.length >= (config.maxTextos || 5)) {
            return;
        }

        textCount += 1;
        const id = 'texto_' + textCount;

        listaTextos.appendChild(buildTextControl(id, listaTextos.children.length + 1));
        dynamicMarkers.appendChild(buildTextMarker(id, listaTextos.children.length));
        bindTextControl(id);
        makeInteractive(document.getElementById('marker_' + id), stage);
        updateCounters();
    });

    btnAgregarFirma?.addEventListener('click', function () {
        if (listaFirmas.children.length >= (config.maxFirmas || 5)) {
            return;
        }

        firmaCount += 1;
        const id = 'firma_' + firmaCount;

        listaFirmas.appendChild(buildFirmaControl(id, listaFirmas.children.length + 1));
        dynamicMarkers.appendChild(buildFirmaMarker(id, listaFirmas.children.length));
        bindFirmaControl(id);
        makeInteractive(document.getElementById('marker_' + id), stage);
        updateCounters();
    });

    btnAgregarQr?.addEventListener('click', function () {
        if (listaQr.children.length >= (config.maxQr || 1)) {
            return;
        }

        qrCount += 1;
        const id = 'qr_' + qrCount;

        listaQr.appendChild(buildQrControl(id));
        dynamicMarkers.appendChild(buildQrMarker(id));
        bindQrControl(id);
        makeInteractive(document.getElementById('marker_' + id), stage);
        updateCounters();
    });

    dynamicMarkers.addEventListener('click', function (event) {
        const removeButton = event.target.closest('[data-remove-marker]');
        if (!removeButton) {
            return;
        }

        removeItem(removeButton.getAttribute('data-remove-marker'));
    });

    function buildTextControl(id, index) {
        const wrapper = document.createElement('div');
        wrapper.className = 'certificado-field-card';
        wrapper.id = 'control_' + id;
        wrapper.innerHTML = `
            <div class="certificado-field-card__header">
                <strong>Texto ${index}</strong>
                <button type="button" class="btn btn-sm btn-link text-danger p-0" data-remove-marker="${id}">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <input type="text" class="form-control form-control-sm" id="input_${id}" value="${config.defaultTextoLabel || 'Texto editable'}">
        `;
        return wrapper;
    }

    function buildFirmaControl(id, index) {
        const wrapper = document.createElement('div');
        wrapper.className = 'certificado-field-card';
        wrapper.id = 'control_' + id;
        wrapper.innerHTML = `
            <div class="certificado-field-card__header">
                <strong>Firma ${index}</strong>
                <button type="button" class="btn btn-sm btn-link text-danger p-0" data-remove-marker="${id}">
                    <i class="fas fa-times"></i>
                </button>
            </div>

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
                    <button type="button" class="btn btn-sm btn-outline-danger" id="clear_${id}">
                        Limpiar
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="apply_${id}">
                        Aplicar firma
                    </button>
                </div>
            </div>

            <div class="certificado-sign-panel d-none" id="panel_upload_${id}">
                <input type="file" class="form-control form-control-sm" id="input_${id}" accept="image/png,image/webp">
                <div class="form-text">Solo PNG o WEBP transparentes.</div>
            </div>
        `;
        return wrapper;
    }

    function buildQrControl(id) {
        const wrapper = document.createElement('div');
        wrapper.className = 'certificado-field-card';
        wrapper.id = 'control_' + id;
        wrapper.innerHTML = `
            <div class="certificado-field-card__header">
                <strong>QR</strong>
                <button type="button" class="btn btn-sm btn-link text-danger p-0" data-remove-marker="${id}">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <input type="text" class="form-control form-control-sm" id="input_${id}" value="${config.defaultQrLabel || 'QR / Verificacion'}">
        `;
        return wrapper;
    }

    function buildTextMarker(id, index) {
        const marker = document.createElement('div');
        marker.className = 'certificado-marker certificado-marker--dynamic certificado-marker--text';
        marker.id = 'marker_' + id;
        marker.style.left = 50 + index * 18 + 'px';
        marker.style.top = 60 + index * 18 + 'px';
        marker.style.width = '260px';
        marker.style.minHeight = '88px';
        marker.innerHTML = `
            <span class="certificado-marker__label">Texto</span>
            <strong class="certificado-marker__value" id="value_${id}">${config.defaultTextoLabel || 'Texto editable'}</strong>
            <button type="button" class="certificado-marker__remove" data-remove-marker="${id}" aria-label="Eliminar">
                <i class="fas fa-times"></i>
            </button>
        `;
        ensureResizeHandle(marker);
        return marker;
    }

    function buildFirmaMarker(id, index) {
        const marker = document.createElement('div');
        marker.className = 'certificado-marker certificado-marker--dynamic certificado-marker--firma';
        marker.id = 'marker_' + id;
        marker.style.left = 80 + index * 22 + 'px';
        marker.style.top = 120 + index * 22 + 'px';
        marker.style.width = '240px';
        marker.style.height = '150px';
        marker.innerHTML = `
            <span class="certificado-marker__label">Firma</span>
            <div class="certificado-marker__signature" id="value_${id}">${config.defaultFirmaLabel || 'Firma'}</div>
            <button type="button" class="certificado-marker__remove" data-remove-marker="${id}" aria-label="Eliminar">
                <i class="fas fa-times"></i>
            </button>
        `;
        ensureResizeHandle(marker);
        return marker;
    }

    function buildQrMarker(id) {
        const marker = document.createElement('div');
        marker.className = 'certificado-marker certificado-marker--dynamic certificado-marker--qr';
        marker.id = 'marker_' + id;
        marker.style.left = '120px';
        marker.style.top = '180px';
        marker.style.width = '170px';
        marker.style.height = '190px';
        marker.innerHTML = `
            <span class="certificado-marker__label">QR</span>
            <div class="certificado-marker__qr-box"><span id="value_${id}">${config.defaultQrLabel || 'QR / Verificacion'}</span></div>
            <button type="button" class="certificado-marker__remove" data-remove-marker="${id}" aria-label="Eliminar">
                <i class="fas fa-times"></i>
            </button>
        `;
        ensureResizeHandle(marker);
        return marker;
    }

    function bindTextControl(id) {
        const input = document.getElementById('input_' + id);
        const value = document.getElementById('value_' + id);

        input?.addEventListener('input', function () {
            value.textContent = input.value.trim() || config.defaultTextoLabel || 'Texto editable';
        });
    }

    function bindFirmaControl(id) {
        const uploadInput = document.getElementById('input_' + id);
        const value = document.getElementById('value_' + id);
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

                    if (item.getAttribute('data-mode') === 'draw') {
                        item.classList.remove('btn-outline-success');
                    }
                });

                if (mode === 'draw') {
                    drawPanel.classList.remove('d-none');
                    uploadPanel.classList.add('d-none');
                    button.classList.add('active', 'btn-success');
                    button.classList.remove('btn-outline-secondary');
                } else {
                    uploadPanel.classList.remove('d-none');
                    drawPanel.classList.add('d-none');
                    button.classList.add('active', 'btn-success');
                    button.classList.remove('btn-outline-secondary');
                }
            });
        });

        clearButton?.addEventListener('click', function () {
            const context = canvas.getContext('2d');
            context.clearRect(0, 0, canvas.width, canvas.height);
            initSignatureCanvas(context, canvas);
            value.textContent = config.defaultFirmaLabel || 'Firma';
        });

        applyButton?.addEventListener('click', function () {
            const dataUrl = canvas.toDataURL('image/png');
            applySignatureImage(value, dataUrl);
        });

        uploadInput?.addEventListener('change', function (event) {
            const [file] = event.target.files || [];

            if (!file) {
                value.textContent = config.defaultFirmaLabel || 'Firma';
                return;
            }

            if (!['image/png', 'image/webp'].includes(file.type)) {
                event.target.value = '';
                value.textContent = config.defaultFirmaLabel || 'Firma';
                alert('Solo se permite PNG o WEBP para la firma.');
                return;
            }

            const reader = new FileReader();

            reader.onload = function (loadEvent) {
                applySignatureImage(value, loadEvent.target.result);
            };

            reader.readAsDataURL(file);
        });
    }

    function bindQrControl(id) {
        const input = document.getElementById('input_' + id);
        const value = document.getElementById('value_' + id);

        input?.addEventListener('input', function () {
            value.textContent = input.value.trim() || config.defaultQrLabel || 'QR / Verificacion';
        });
    }

    function applySignatureImage(container, src) {
        container.innerHTML = '';

        const image = document.createElement('img');
        image.src = src;
        image.alt = 'Firma cargada';

        container.appendChild(image);
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
        document.getElementById('control_' + id)?.remove();
        document.getElementById('marker_' + id)?.remove();
        updateCounters();
    }

    function updateCounters() {
        contadorTextos.textContent = `${listaTextos.children.length} / ${config.maxTextos || 5}`;
        contadorFirmas.textContent = `${listaFirmas.children.length} / ${config.maxFirmas || 5}`;
        contadorQr.textContent = `${listaQr.children.length} / ${config.maxQr || 1}`;

        btnAgregarTexto.disabled = listaTextos.children.length >= (config.maxTextos || 5);
        btnAgregarFirma.disabled = listaFirmas.children.length >= (config.maxFirmas || 5);
        btnAgregarQr.disabled = listaQr.children.length >= (config.maxQr || 1);
    }

    function ensureResizeHandle(marker) {
        if (marker.querySelector('.certificado-marker__resize-handle')) {
            return;
        }

        const handles = [
            'n',
            's',
            'e',
            'w',
            'ne',
            'nw',
            'se',
            'sw',
        ];

        handles.forEach(function (direction) {
            const handle = document.createElement('button');
            handle.type = 'button';
            handle.className = 'certificado-marker__resize-handle certificado-marker__resize-handle--' + direction;
            handle.setAttribute('aria-label', 'Cambiar tamano');
            handle.setAttribute('data-resize-direction', direction);
            marker.appendChild(handle);
        });
    }

    function makeInteractive(marker, container) {
        let mode = null;
        let resizeDirection = null;
        let startX = 0;
        let startY = 0;
        let initialLeft = 0;
        let initialTop = 0;
        let initialWidth = 0;
        let initialHeight = 0;
        marker.style.cursor = 'move';

        marker.addEventListener('mousedown', startMove);
        marker.addEventListener('touchstart', startMove, { passive: false });
        marker.querySelectorAll('.certificado-marker__resize-handle').forEach(function (resizeHandle) {
            resizeHandle.addEventListener('mousedown', startResize);
            resizeHandle.addEventListener('touchstart', startResize, { passive: false });
        });

        function startMove(event) {
            if (event.target.closest('.certificado-marker__resize-handle, .certificado-marker__remove')) {
                return;
            }

            mode = 'move';

            const point = getPoint(event);
            const containerRect = container.getBoundingClientRect();
            const markerRect = marker.getBoundingClientRect();

            startX = point.clientX;
            startY = point.clientY;
            initialLeft = markerRect.left - containerRect.left;
            initialTop = markerRect.top - containerRect.top;

            marker.classList.add('is-dragging');
            bindDocumentEvents();
            event.preventDefault();
        }

        function startResize(event) {
            mode = 'resize';
            resizeDirection = event.currentTarget.getAttribute('data-resize-direction') || 'se';

            const point = getPoint(event);
            const containerRect = container.getBoundingClientRect();
            const markerRect = marker.getBoundingClientRect();

            startX = point.clientX;
            startY = point.clientY;
            initialLeft = markerRect.left - containerRect.left;
            initialTop = markerRect.top - containerRect.top;
            initialWidth = markerRect.width;
            initialHeight = markerRect.height;

            marker.classList.add('is-resizing');
            bindDocumentEvents();
            event.preventDefault();
            event.stopPropagation();
        }

        function bindDocumentEvents() {
            document.addEventListener('mousemove', onPointerMove);
            document.addEventListener('mouseup', stopInteraction);
            document.addEventListener('touchmove', onPointerMove, { passive: false });
            document.addEventListener('touchend', stopInteraction);
        }

        function unbindDocumentEvents() {
            document.removeEventListener('mousemove', onPointerMove);
            document.removeEventListener('mouseup', stopInteraction);
            document.removeEventListener('touchmove', onPointerMove);
            document.removeEventListener('touchend', stopInteraction);
        }

        function onPointerMove(event) {
            if (!mode) {
                return;
            }

            const point = getPoint(event);
            const deltaX = point.clientX - startX;
            const deltaY = point.clientY - startY;

            if (mode === 'move') {
                const bounded = getBoundedPosition(initialLeft + deltaX, initialTop + deltaY, marker, container);
                marker.style.left = bounded.left + 'px';
                marker.style.top = bounded.top + 'px';
                marker.style.right = 'auto';
                marker.style.bottom = 'auto';
            }

            if (mode === 'resize') {
                const box = getResizedBox({
                    direction: resizeDirection,
                    deltaX,
                    deltaY,
                    initialLeft,
                    initialTop,
                    initialWidth,
                    initialHeight,
                    container
                });

                marker.style.left = box.left + 'px';
                marker.style.top = box.top + 'px';
                marker.style.width = box.width + 'px';
                marker.style.height = box.height + 'px';
                marker.style.right = 'auto';
                marker.style.bottom = 'auto';
            }

            event.preventDefault();
        }

        function stopInteraction() {
            mode = null;
            resizeDirection = null;
            marker.classList.remove('is-dragging');
            marker.classList.remove('is-resizing');
            unbindDocumentEvents();
        }
    }

    function getPoint(event) {
        if (event.touches && event.touches.length > 0) {
            return event.touches[0];
        }

        return event;
    }

    function getBoundedPosition(left, top, marker, container) {
        const containerRect = container.getBoundingClientRect();
        const markerRect = marker.getBoundingClientRect();

        return {
            left: Math.max(0, Math.min(left, containerRect.width - markerRect.width)),
            top: Math.max(0, Math.min(top, containerRect.height - markerRect.height))
        };
    }

    function getResizedBox({
        direction,
        deltaX,
        deltaY,
        initialLeft,
        initialTop,
        initialWidth,
        initialHeight,
        container
    }) {
        const containerRect = container.getBoundingClientRect();
        const minWidth = 120;
        const minHeight = 70;

        let left = initialLeft;
        let top = initialTop;
        let width = initialWidth;
        let height = initialHeight;

        if (direction.includes('e')) {
            width = initialWidth + deltaX;
        }

        if (direction.includes('s')) {
            height = initialHeight + deltaY;
        }

        if (direction.includes('w')) {
            left = initialLeft + deltaX;
            width = initialWidth - deltaX;
        }

        if (direction.includes('n')) {
            top = initialTop + deltaY;
            height = initialHeight - deltaY;
        }

        if (width < minWidth) {
            if (direction.includes('w')) {
                left -= (minWidth - width);
            }
            width = minWidth;
        }

        if (height < minHeight) {
            if (direction.includes('n')) {
                top -= (minHeight - height);
            }
            height = minHeight;
        }

        if (left < 0) {
            if (direction.includes('w')) {
                width += left;
            }
            left = 0;
        }

        if (top < 0) {
            if (direction.includes('n')) {
                height += top;
            }
            top = 0;
        }

        if (left + width > containerRect.width) {
            if (direction.includes('w')) {
                left = containerRect.width - width;
            } else {
                width = containerRect.width - left;
            }
        }

        if (top + height > containerRect.height) {
            if (direction.includes('n')) {
                top = containerRect.height - height;
            } else {
                height = containerRect.height - top;
            }
        }

        width = Math.max(minWidth, Math.min(width, containerRect.width - left));
        height = Math.max(minHeight, Math.min(height, containerRect.height - top));

        return {
            left,
            top,
            width,
            height
        };
    }

    updateCounters();
});
