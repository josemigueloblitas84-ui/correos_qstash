document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('reporteAgendaInformeForm');
    const agendaWrapper = document.getElementById('agendaTableWrapper');
    const informeWrapper = document.getElementById('informeAgendaWrapper');
    const agendaPreviewModalElement = document.getElementById('modalPrevisualizarAgendaReporte');
    const agendaPreviewFrame = document.getElementById('agendaPreviewFrameReporte');
    const config = window.reporteAgendaInformeConfig || {};
    const csrfToken = config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const agendaPreviewModal = agendaPreviewModalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal
        ? bootstrap.Modal.getOrCreateInstance(agendaPreviewModalElement)
        : null;

    if (!form || !config.dataUrl || !config.dataInformeUrl) {
        return;
    }

    let searchWasSubmitted = false;
    const canValidateInforme = Boolean(config.canValidateInforme);
    const fechaDesdeField = document.getElementById('fecha_desde');
const fechaHastaField = document.getElementById('fecha_hasta');

    if (typeof $ !== 'undefined' && $.datepicker) {
        $.datepicker.setDefaults({
            closeText: 'Cerrar',
            prevText: 'Anterior',
            nextText: 'Siguiente',
            currentText: 'Hoy',
            monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
            dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
            dayNamesMin: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
            weekHeader: 'Sm',
            firstDay: 1
        });

        $(fechaDesdeField).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            onSelect: function (selectedDate) {
                $(fechaHastaField).datepicker('option', 'minDate', selectedDate);
            }
        });

        $(fechaHastaField).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });

        if (fechaDesdeField?.value) {
            $(fechaDesdeField).datepicker('setDate', fechaDesdeField.value);
            $(fechaHastaField).datepicker('option', 'minDate', fechaDesdeField.value);
        }

        if (fechaHastaField?.value) {
            $(fechaHastaField).datepicker('setDate', fechaHastaField.value);
        }
    }

    function getFilters() {
        return {
            fecha_desde: document.getElementById('fecha_desde')?.value || '',
            fecha_hasta: document.getElementById('fecha_hasta')?.value || '',
            equipo: document.getElementById('equipo')?.value || '',
            tipo_busqueda: document.querySelector('input[name="tipo_busqueda"]:checked')?.value || 'agenda'
        };
    }

    function emptyDataTableResponse(draw) {
        return {
            draw: draw || 0,
            recordsTotal: 0,
            recordsFiltered: 0,
            data: []
        };
    }

    function buildSearchOnlyAjax(url) {
        return function (data, callback) {
            if (!searchWasSubmitted) {
                callback(emptyDataTableResponse(data.draw));
                return;
            }

            $.ajax({
                url: url,
                data: Object.assign({}, data, getFilters()),
                dataType: 'json',
                success: callback,
                error: function () {
                    callback(emptyDataTableResponse(data.draw));
                }
            });
        };
    }

    const tableAgenda = $('#tablaReporteAgenda').DataTable({
        processing: true,
        serverSide: true,
        deferLoading: 0,
        searching: false,
        ajax: buildSearchOnlyAjax(config.dataUrl),
        columns: [
            { data: 'fecha_mostrar', name: 'a.fecha' },
            { data: 'nombre_mostrar', name: 'u.name' },
            { data: 'equipo_mostrar', name: 'd.nombre_depa' },
            { data: 'fechas_agendadas', name: 'a.fecha_desde', orderable: false, searchable: false },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
        ],
        order: [[0, 'asc'], [1, 'asc'], [2, 'asc']],
        language: {
            url: '/assets/datatables/i18n/es-ES.json'
        }
    });

    const informeColumns = [
        { data: 'fecha_mostrar', name: 'fecha_actividad' },
        { data: 'nombre_mostrar', name: 'usuario_nombre' },
        { data: 'equipo_mostrar', name: 'equipo_nombre' },
        { data: 'visualizar', name: 'visualizar', orderable: false, searchable: false }
    ];

    if (canValidateInforme) {
        informeColumns.push({ data: 'validar', name: 'validar', orderable: false, searchable: false });
    }

    const tableInforme = $('#tablaReporteInformeAgenda').DataTable({
        processing: true,
        serverSide: true,
        deferLoading: 0,
        searching: false,
        ajax: buildSearchOnlyAjax(config.dataInformeUrl),
        columns: informeColumns,
        order: [[0, 'asc'], [1, 'asc'], [2, 'asc']],
        language: {
            url: '/assets/datatables/i18n/es-ES.json'
        }
    });

    function syncModeView() {
        const tipoBusqueda = document.querySelector('input[name="tipo_busqueda"]:checked')?.value || 'agenda';

        if (agendaWrapper) {
            agendaWrapper.classList.toggle('d-none', tipoBusqueda !== 'agenda');
        }

        if (informeWrapper) {
            informeWrapper.classList.toggle('d-none', tipoBusqueda !== 'informe');
        }
    }

    async function validarInforme(payload) {
        const response = await fetch(config.validarInformeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(data.message || 'No se pudo validar el informe.');
        }

        return data;
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        const tipoBusqueda = document.querySelector('input[name="tipo_busqueda"]:checked')?.value || 'agenda';

        searchWasSubmitted = true;
        syncModeView();

        if (tipoBusqueda === 'agenda') {
            tableAgenda.ajax.reload();
        }

        if (tipoBusqueda === 'informe') {
            tableInforme.ajax.reload();
        }
    });

    document.querySelectorAll('input[name="tipo_busqueda"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            syncModeView();
        });
    });

    $('#tablaReporteAgenda').on('click', '.btn-preview-agenda', function () {
        const agendaId = this.dataset.id;

        if (!agendaId || !config.agendaPreviewUrlTemplate) {
            Swal.fire({
                icon: 'warning',
                title: 'Agenda no disponible',
                text: 'No se pudo identificar la agenda seleccionada.',
            });
            return;
        }

        Swal.fire({
            title: 'Cargando...',
            text: 'Generando el PDF de la agenda',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        if (agendaPreviewFrame) {
            agendaPreviewFrame.onload = function () {
                Swal.close();
                agendaPreviewFrame.onload = null;
            };

            agendaPreviewFrame.src = `${config.agendaPreviewUrlTemplate.replace('__ID__', agendaId)}?t=${Date.now()}`;
        }

        agendaPreviewModal?.show();
    });

    $('#tablaReporteInformeAgenda').on('click', '.btn-preview-informe', function () {
        const fecha = this.dataset.fecha;
        const usuarioId = this.dataset.usuario;

        if (!fecha || !usuarioId || !config.informePreviewUrl) {
            Swal.fire({
                icon: 'warning',
                title: 'Informe no disponible',
                text: 'No se pudo identificar el informe seleccionado.',
            });
            return;
        }

        const url = new URL(config.informePreviewUrl, window.location.origin);
        url.searchParams.set('fecha', fecha);
        url.searchParams.set('usuario_id', usuarioId);
        url.searchParams.set('t', Date.now());

        Swal.fire({
            title: 'Cargando...',
            text: 'Generando el PDF del informe',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        if (agendaPreviewFrame) {
            agendaPreviewFrame.onload = function () {
                Swal.close();
                agendaPreviewFrame.onload = null;
            };

            agendaPreviewFrame.src = url.toString();
        }

        agendaPreviewModal?.show();
    });

    $('#tablaReporteInformeAgenda').on('click', '.btn-validar', async function () {
        const fecha = this.dataset.fecha;
        const usuarioId = this.dataset.usuario;

        if (!fecha || !usuarioId || !config.validarInformeUrl) {
            Swal.fire({
                icon: 'warning',
                title: 'Informe no disponible',
                text: 'No se pudo identificar el informe a validar.',
            });
            return;
        }

        const result = await Swal.fire({
            title: '¿Validar informe?',
            text: 'Se registrarán las horas validadas y el informe quedará marcado como validado.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, validar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
        });

        if (!result.isConfirmed) {
            return;
        }

        Swal.fire({
            title: 'Validando...',
            text: 'Guardando la validación del informe',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        try {
            const response = await validarInforme({
                fecha: fecha,
                usuario_id: usuarioId,
            });

            Swal.fire({
                icon: 'success',
                title: 'Correcto',
                text: response.message || 'Informe validado correctamente.',
            });

            tableInforme.ajax.reload(null, false);
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'No se pudo validar el informe.',
            });
        }
    });

    if (agendaPreviewModalElement && agendaPreviewFrame) {
        agendaPreviewModalElement.addEventListener('hidden.bs.modal', function () {
            agendaPreviewFrame.src = 'about:blank';
        });
    }

    syncModeView();
});
