document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('articuloWizardFormV2');

    if (!form) {
        return;
    }

    const config = window.formV2Config || {};
    const agendaValidateButton = form.querySelector('[data-action="validate-agenda"]');
    const agendaPreviewWrapper = document.getElementById('agendaPreviewWrapper');
    const agendaWeeklyWrapper = document.getElementById('agendaWeeklyWrapper');
    const agendaActivitiesHistoryWrapper = document.getElementById('agendaActivitiesHistoryWrapper');
    const agendaTable = document.getElementById('agendaRegistradaTable');
    const agendaDailyActivitiesTableElement = document.getElementById('agendaDailyActivitiesTable');
    const agendaWeeklyActivitiesTableElement = document.getElementById('agendaWeeklyActivitiesTable');
    const fechaField = form.querySelector('[name="fecha"]');
    const periodoDelField = form.querySelector('[name="fecha_desde"]');
    const periodoAlField = form.querySelector('[name="fecha_hasta"]');
    const departamentoField = form.querySelector('[name="cod_unidad"]');
    const solicitanteField = form.querySelector('[name="cod_solicitante"]');
    const agendaWeekStartField = document.getElementById('agendaWeekStart');
    const agendaWeekEndField = document.getElementById('agendaWeekEnd');
    const agendaDailyFromHourField = document.getElementById('agendaDailyFromHour');
    const agendaDailyFromMinuteField = document.getElementById('agendaDailyFromMinute');
    const cargoVisualField = document.getElementById('cargo_visual');
    const agendaDailyToHourField = document.getElementById('agendaDailyToHour');
    const agendaDailyToMinuteField = document.getElementById('agendaDailyToMinute');
    const agendaActivityNameField = document.getElementById('agendaActivityName');
    const agendaEquipoField = document.getElementById('agendaEquipo');
    const agendaRegisterActivityButton = document.getElementById('agendaRegisterActivityButton');
    const agendaPreviewButton = document.getElementById('agendaPreviewButton');
    const agendaSendButton = document.getElementById('agendaSendButton');
    const agendaPreviewModalElement = document.getElementById('modalPrevisualizarAgenda');
    const agendaPreviewFrame = document.getElementById('agendaPreviewFrame');
    const editActivityModalElement = document.getElementById('modalEditarActividadAgenda');
    const editActivityForm = document.getElementById('formEditarActividadAgenda');
    const editActivityIdField = document.getElementById('editActivityId');
    const editActivityAgendaIdField = document.getElementById('editActivityAgendaId');
    const editActivityTypeField = document.getElementById('editActivityType');
    const editActivityNameField = document.getElementById('editActivityName');
    const editActivityDepartmentField = document.getElementById('editActivityDepartment');
    const editActivityDailyFields = document.getElementById('editActivityDailyFields');
    const editActivityWeeklyFields = document.getElementById('editActivityWeeklyFields');
    const editActivityWeekStartField = document.getElementById('editActivityWeekStart');
    const editActivityWeekEndField = document.getElementById('editActivityWeekEnd');
    const editActivityDailyFromHourField = document.getElementById('editActivityDailyFromHour');
    const editActivityDailyFromMinuteField = document.getElementById('editActivityDailyFromMinute');
    const editActivityDailyToHourField = document.getElementById('editActivityDailyToHour');
    const editActivityDailyToMinuteField = document.getElementById('editActivityDailyToMinute');
    const editActivityModalLabel = document.getElementById('modalEditarActividadAgendaLabel');
    const editActivityModalSubtitle = document.getElementById('editActivityModalSubtitle');
    const activityModeInputs = Array.from(form.querySelectorAll('input[name="agenda_activity_mode"]'));
    const agendaDailyCard = document.getElementById('agendaDailyCard');
    const agendaWeeklyCard = document.getElementById('agendaWeeklyCard');
    const agendaDailyBody = document.getElementById('agendaDailyBody');
    const agendaWeeklyBody = document.getElementById('agendaWeeklyBody');
    const csrfToken = form.querySelector('input[name="_token"]')?.value || '';
    const agendaHeaderFields = [
        'fecha',
        'cod_unidad',
        'cod_solicitante',
        'fecha_desde',
        'fecha_hasta',
        'hora_inicio_hora',
        'hora_inicio_minuto',
        'hora_fin_hora',
        'hora_fin_minuto',
    ]
        .map((name) => form.querySelector(`[name="${name}"]`))
        .filter(Boolean);

    let agendarDataTable = null;
    let agendaDailyActivitiesDataTable = null;
    let agendaWeeklyActivitiesDataTable = null;
    let agendaReady = false;
    let isSubmittingAgenda = false;
    let isSubmittingActivity = false;
    let isSubmittingActivityEdit = false;
    let isLoadingAgendaRecord = false;
    let currentAgendaId = null;
    let currentAgendaContext = null;
    let activityAutocompleteTimer = null;
    let latestAgendaRequestToken = 0;
    const agendaPreviewModal = agendaPreviewModalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal
        ? bootstrap.Modal.getOrCreateInstance(agendaPreviewModalElement)
        : null;
    const editActivityModal = editActivityModalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal
        ? bootstrap.Modal.getOrCreateInstance(editActivityModalElement)
        : null;

    const formValidationConfig = {
        fecha: {
            label: 'fecha',
            required: true,
            message: 'La fecha es obligatoria.',
        },
        cod_unidad: {
            label: 'departamento o unidad',
            required: true,
            message: 'Debes seleccionar un departamento o unidad.',
        },
        cod_solicitante: {
            label: 'nombre y apellido',
            required: true,
            message: 'Debes seleccionar un usuario.',
        },
        fecha_desde: {
            label: 'periodo del',
            required: true,
            custom: (value) => {
                const todayString = config.todayString || '';
                const isoValue = getIsoValue({ value });

                if (!isoValue) {
                    return 'La fecha inicial no tiene un formato válido.';
                }

                return isoValue >= todayString || 'El periodo inicial debe ser mayor o igual a la fecha actual.';
            },
            message: 'El periodo inicial es obligatorio.',
        },
        fecha_hasta: {
            label: 'periodo al',
            required: true,
            custom: (value) => {
                const periodoDel = getIsoValue(periodoDelField);
                const periodoAl = getIsoValue({ value });

                if (!periodoDel) {
                    return 'Primero debes seleccionar el periodo inicial.';
                }

                if (!periodoAl) {
                    return 'La fecha final no tiene un formato válido.';
                }

                if (periodoAl < periodoDel) {
                    return 'El periodo final debe ser mayor o igual al periodo inicial';
                }

                return isSameMonth(periodoDel, periodoAl) || 'El periodo final debe pertenecer al mismo mes que el periodo inicial.';
            },
            message: 'El periodo final es obligatorio.',
        },
        hora_inicio_hora: {
            label: 'hora de inicio',
            required: true,
            custom: (value) => {
                const minuto = form.querySelector('[name="hora_inicio_minuto"]')?.value;

                if (!value || !minuto) {
                    return 'Debes seleccionar la hora de inicio completa.';
                }

                return true;
            },
        },
        hora_inicio_minuto: {
            label: 'minuto de inicio',
            required: true,
            custom: (value) => {
                const hora = form.querySelector('[name="hora_inicio_hora"]')?.value;

                if (!hora || !value) {
                    return 'Debes seleccionar la hora de inicio completa.';
                }

                return true;
            },
        },
        hora_fin_hora: {
            label: 'hora final',
            required: true,
            custom: (value) => {
                const horaInicio = form.querySelector('[name="hora_inicio_hora"]')?.value;
                const minutoInicio = form.querySelector('[name="hora_inicio_minuto"]')?.value;
                const minutoFin = form.querySelector('[name="hora_fin_minuto"]')?.value;

                if (!value || !minutoFin) {
                    return 'Debes seleccionar la hora final completa.';
                }

                if (!horaInicio || !minutoInicio) {
                    return 'Primero debes seleccionar la hora de inicio.';
                }

                const inicio = `${horaInicio}:${minutoInicio}`;
                const fin = `${value}:${minutoFin}`;

                return fin > inicio || 'La hora final debe ser mayor a la hora de inicio.';
            },
        },
        hora_fin_minuto: {
            label: 'minuto final',
            required: true,
            custom: (value) => {
                const horaInicio = form.querySelector('[name="hora_inicio_hora"]')?.value;
                const minutoInicio = form.querySelector('[name="hora_inicio_minuto"]')?.value;
                const horaFin = form.querySelector('[name="hora_fin_hora"]')?.value;

                if (!horaFin || !value) {
                    return 'Debes seleccionar la hora final completa.';
                }

                if (!horaInicio || !minutoInicio) {
                    return 'Primero debes seleccionar la hora de inicio.';
                }

                const inicio = `${horaInicio}:${minutoInicio}`;
                const fin = `${horaFin}:${value}`;

                return fin > inicio || 'La hora final debe ser mayor a la hora de inicio.';
            },
        },
    };

    const getMonthEndDate = (dateString) => {
        if (!dateString) {
            return '';
        }

        const [year, month] = dateString.split('-').map(Number);

        if (!year || !month) {
            return '';
        }

        const lastDay = new Date(year, month, 0).getDate();

        return `${year}-${String(month).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`;
    };

    const parseIsoDate = (dateString) => {
        if (!dateString) {
            return null;
        }

        const [year, month, day] = dateString.split('-').map(Number);

        if (!year || !month || !day) {
            return null;
        }

        return new Date(year, month - 1, day);
    };

    const parseDisplayDate = (value) => {
        const parts = (value || '').trim().split('/');

        if (parts.length !== 3) {
            return '';
        }

        const [day, month, year] = parts;

        if (!day || !month || !year) {
            return '';
        }

        return `${year}-${month}-${day}`;
    };

    const getIsoValue = (field) => {
        if (!field) {
            return '';
        }

        const value = (field.value || '').trim();

        if (!value) {
            return '';
        }

        if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
            return value;
        }

        if (/^\d{2}\/\d{2}\/\d{4}$/.test(value)) {
            return parseDisplayDate(value);
        }

        return '';
    };

    const isSameMonth = (firstDate, secondDate) => {
        if (!firstDate || !secondDate) {
            return false;
        }

        return firstDate.slice(0, 7) === secondDate.slice(0, 7);
    };

    const applyDatepickerRange = (field, options = {}) => {
        if (!field || typeof $ === 'undefined' || !$.datepicker) {
            return;
        }

        const $field = $(field);

        if (Object.prototype.hasOwnProperty.call(options, 'minDate')) {
            $field.datepicker('option', 'minDate', options.minDate ?? null);
        }

        if (Object.prototype.hasOwnProperty.call(options, 'maxDate')) {
            $field.datepicker('option', 'maxDate', options.maxDate ?? null);
        }
    };

    const initializeDatepicker = (field, extraOptions = {}) => {
        if (!field || typeof $ === 'undefined' || !$.datepicker) {
            return;
        }

        const initialIsoValue = getIsoValue(field);

        $(field).datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            firstDay: 1,
            ...extraOptions,
            onSelect: function () {
                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.dispatchEvent(new Event('change', { bubbles: true }));

                if (typeof extraOptions.onSelect === 'function') {
                    extraOptions.onSelect.apply(this, arguments);
                }
            },
        });

        if (initialIsoValue) {
            $(field).datepicker('setDate', parseIsoDate(initialIsoValue));
        }
    };

    const getAgendaContextFromForm = () => ({
        fecha: getIsoValue(fechaField),
        fechaDesde: getIsoValue(periodoDelField),
        fechaHasta: getIsoValue(periodoAlField),
        horaDesde: `${form.querySelector('[name="hora_inicio_hora"]')?.value || ''}:${form.querySelector('[name="hora_inicio_minuto"]')?.value || ''}`,
        horaHasta: `${form.querySelector('[name="hora_fin_hora"]')?.value || ''}:${form.querySelector('[name="hora_fin_minuto"]')?.value || ''}`,
    });

    const parseAgendaContextFromRow = (trigger) => {
        const row = trigger.closest('tr');

        if (!row) {
            return null;
        }

        const cells = row.querySelectorAll('td');

        if (cells.length < 6) {
            return null;
        }

        const periodoText = cells[4]?.textContent?.trim() || '';
        const horarioText = cells[5]?.textContent?.trim() || '';
        const [periodoDesdeText = '', periodoHastaText = ''] = periodoText.split(' al ');
        const [horaDesdeText = '', horaHastaText = ''] = horarioText.split(' al ');

        return {
            fecha: parseDisplayDate(cells[1]?.textContent?.trim() || ''),
            fechaDesde: parseDisplayDate(periodoDesdeText),
            fechaHasta: parseDisplayDate(periodoHastaText),
            horaDesde: horaDesdeText.trim(),
            horaHasta: horaHastaText.trim(),
        };
    };

    const syncPeriodoAlRange = () => {
        if (!periodoDelField || !periodoAlField) {
            return;
        }

        const periodoDelValue = getIsoValue(periodoDelField) || periodoDelField.min;
        const periodoAlMax = getMonthEndDate(periodoDelValue);

        if (periodoDelValue) {
            periodoAlField.min = periodoDelValue;
        }

        if (periodoAlMax) {
            periodoAlField.max = periodoAlMax;
        }

        applyDatepickerRange(periodoAlField, {
            minDate: parseIsoDate(periodoDelValue),
            maxDate: parseIsoDate(periodoAlMax),
        });

        if (periodoAlField.value) {
            const periodoAlValue = getIsoValue(periodoAlField);

            if (periodoAlValue && periodoAlValue < periodoAlField.min) {
                $(periodoAlField).datepicker('setDate', parseIsoDate(periodoAlField.min));
            }

            if (periodoAlField.max && periodoAlValue && periodoAlValue > periodoAlField.max) {
                $(periodoAlField).datepicker('setDate', parseIsoDate(periodoAlField.max));
            }
        }
    };

    const syncAgendaWeekEndRange = () => {
        if (!agendaWeekStartField || !agendaWeekEndField) {
            return;
        }

        const formContext = getAgendaContextFromForm();
        const context = {
            fechaDesde: currentAgendaContext?.fechaDesde || formContext.fechaDesde || '',
            fechaHasta: currentAgendaContext?.fechaHasta || formContext.fechaHasta || '',
        };
        const weekStartMin = context.fechaDesde || '';
        const weekEndMax = context.fechaHasta || getMonthEndDate(agendaWeekStartField.value);
        if (weekStartMin) {
            agendaWeekStartField.min = weekStartMin;
        }

        if (weekEndMax) {
            agendaWeekStartField.max = weekEndMax;
        }

        applyDatepickerRange(agendaWeekStartField, {
            minDate: parseIsoDate(weekStartMin),
            maxDate: parseIsoDate(weekEndMax),
        });

        let weekStartValue = getIsoValue(agendaWeekStartField);

        if (weekStartMin && weekStartValue && weekStartValue < weekStartMin) {
            setDatepickerValue(agendaWeekStartField, weekStartMin);
            weekStartValue = weekStartMin;
        }

        if (weekEndMax && weekStartValue && weekStartValue > weekEndMax) {
            setDatepickerValue(agendaWeekStartField, weekEndMax);
            weekStartValue = weekEndMax;
        }

        weekStartValue = weekStartValue || weekStartMin;

        if (weekStartValue) {
            agendaWeekEndField.min = weekStartValue;
        }

        if (weekEndMax) {
            agendaWeekEndField.max = weekEndMax;
        }

        applyDatepickerRange(agendaWeekEndField, {
            minDate: parseIsoDate(weekStartValue),
            maxDate: parseIsoDate(weekEndMax),
        });

        if (agendaWeekEndField.value) {
            const agendaWeekEndValue = getIsoValue(agendaWeekEndField);

            if (agendaWeekEndValue && agendaWeekEndValue < agendaWeekEndField.min) {
                $(agendaWeekEndField).datepicker('setDate', parseIsoDate(agendaWeekEndField.min));
            }

            if (agendaWeekEndField.max && agendaWeekEndValue && agendaWeekEndValue > agendaWeekEndField.max) {
                $(agendaWeekEndField).datepicker('setDate', parseIsoDate(agendaWeekEndField.max));
            }
        }
    };

    const fillWeeklyAgendaForm = () => {
        const formContext = getAgendaContextFromForm();
        const context = currentAgendaContext || formContext;
        const periodoDel = context.fechaDesde || formContext.fechaDesde || '';
        const periodoAl = context.fechaHasta || formContext.fechaHasta || '';
        const horaDesde = context.horaDesde || formContext.horaDesde || '';
        const horaHasta = context.horaHasta || formContext.horaHasta || '';
        const [horaInicioHora = '', horaInicioMinuto = ''] = horaDesde.split(':');
        const [horaFinHora = '', horaFinMinuto = ''] = horaHasta.split(':');
        const cargo = cargoVisualField?.value || '';

        if (agendaWeekStartField) {
            agendaWeekStartField.min = periodoDel || '';
            agendaWeekStartField.max = periodoAl || '';
            applyDatepickerRange(agendaWeekStartField, {
                minDate: parseIsoDate(periodoDel),
                maxDate: parseIsoDate(periodoAl),
            });
            setDatepickerValue(agendaWeekStartField, periodoDel);
        }

        if (agendaWeekEndField) {
            agendaWeekEndField.min = periodoDel || '';
            agendaWeekEndField.max = periodoAl || '';
            applyDatepickerRange(agendaWeekEndField, {
                minDate: parseIsoDate(periodoDel),
                maxDate: parseIsoDate(periodoAl),
            });
            setDatepickerValue(agendaWeekEndField, periodoAl);
        }

        syncAgendaWeekEndRange();

        if (agendaDailyFromHourField) {
            agendaDailyFromHourField.value = horaInicioHora;
        }

        if (agendaDailyFromMinuteField) {
            agendaDailyFromMinuteField.value = horaInicioMinuto;
        }

        if (agendaDailyToHourField) {
            agendaDailyToHourField.value = horaFinHora;
        }

        if (agendaDailyToMinuteField) {
            agendaDailyToMinuteField.value = horaFinMinuto;
        }

        if (agendaActivityNameField && !agendaActivityNameField.value.trim()) {
            agendaActivityNameField.value = cargo;
        }

        if (agendaEquipoField && !agendaEquipoField.value) {
            agendaEquipoField.value = departamentoField?.value || '';
        }
    };

    const setDatepickerValue = (field, isoDate) => {
        if (!field) {
            return;
        }

        if (typeof $ !== 'undefined' && $.datepicker) {
            $(field).datepicker('setDate', parseIsoDate(isoDate));
            return;
        }

        field.value = isoDate || '';
    };

    const syncProgrammaticFieldUpdate = (field) => {
        if (!field) {
            return;
        }

        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
    };

    const applyTimeToHeaderFields = (timeValue, hourFieldName, minuteFieldName) => {
        const [hour = '', minute = ''] = (timeValue || '').split(':');
        const hourField = form.querySelector(`[name="${hourFieldName}"]`);
        const minuteField = form.querySelector(`[name="${minuteFieldName}"]`);

        if (hourField) {
            hourField.value = hour;
        }

        if (minuteField) {
            minuteField.value = minute;
        }
    };

    const syncCargoVisualWithSelectedUser = () => {
        const selectedOption = solicitanteField?.options[solicitanteField.selectedIndex];
        const tipoPersonal = selectedOption?.dataset?.tipoPersonal || '';

        if (cargoVisualField) {
            cargoVisualField.value = tipoPersonal;
        }
    };

    const fetchAgendaById = async (agendaId) => {
        const response = await fetch(config.agendaShowUrl.replace('__ID__', agendaId), {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const responseData = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(responseData.message || 'No se pudo cargar la agenda.');
        }

        return responseData;
    };

    const submitAgendaSend = async (agendaId) => {
        const response = await fetch(config.agendaSendUrl.replace('__ID__', agendaId), {
            method: 'PATCH',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const responseData = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(responseData.message || 'No se pudo enviar la agenda.');
        }

        return responseData;
    };

    const submitAgendaDelete = async (agendaId) => {
        const response = await fetch(config.agendaDestroyUrl.replace('__ID__', agendaId), {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const responseData = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(responseData.message || 'No se pudo eliminar la agenda.');
        }

        return responseData;
    };

    const populateAgendaForm = async (agenda) => {
        isLoadingAgendaRecord = true;

        try {
            currentAgendaId = agenda.id;
            currentAgendaContext = {
                fecha: agenda.fecha,
                fechaDesde: agenda.fecha_desde,
                fechaHasta: agenda.fecha_hasta,
                horaDesde: agenda.hora_desde,
                horaHasta: agenda.hora_hasta,
            };

            setDatepickerValue(fechaField, agenda.fecha);
            setDatepickerValue(periodoDelField, agenda.fecha_desde);
            setDatepickerValue(periodoAlField, agenda.fecha_hasta);
            syncProgrammaticFieldUpdate(fechaField);
            syncProgrammaticFieldUpdate(periodoDelField);
            syncProgrammaticFieldUpdate(periodoAlField);
            syncPeriodoAlRange();

            if (departamentoField) {
                departamentoField.value = String(agenda.cod_unidad ?? '');
            }

            await cargarUsuariosPorDepartamento(agenda.cod_unidad, agenda.cod_solicitante);

            if (solicitanteField) {
                solicitanteField.value = String(agenda.cod_solicitante ?? '');
            }

            syncCargoVisualWithSelectedUser();

            applyTimeToHeaderFields(agenda.hora_desde, 'hora_inicio_hora', 'hora_inicio_minuto');
            applyTimeToHeaderFields(agenda.hora_hasta, 'hora_fin_hora', 'hora_fin_minuto');

            agendaReady = true;
        } finally {
            isLoadingAgendaRecord = false;
        }
    };

    const showWeeklyAgendaForm = () => {
        fillWeeklyAgendaForm();

        if (agendaWeeklyWrapper) {
            agendaWeeklyWrapper.classList.remove('d-none');
            agendaWeeklyWrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    const hideWeeklyAgendaForm = () => {
        if (agendaWeeklyWrapper) {
            agendaWeeklyWrapper.classList.add('d-none');
        }
    };

    const hideAgendaPreview = () => {
        if (agendaPreviewWrapper) {
            agendaPreviewWrapper.classList.add('d-none');
        }
    };

    const syncActivityMode = () => {
        const selectedMode = activityModeInputs.find((input) => input.checked)?.value || 'daily';
        const isWeeklyMode = selectedMode === 'weekly';

        [agendaWeekStartField, agendaWeekEndField].forEach((field) => {
            if (field) {
                field.disabled = !isWeeklyMode;
            }
        });

        [
            agendaDailyFromHourField,
            agendaDailyFromMinuteField,
            agendaDailyToHourField,
            agendaDailyToMinuteField,
        ].forEach((field) => {
            if (field) {
                field.disabled = isWeeklyMode;
            }
        });

        if (agendaDailyCard) {
            agendaDailyCard.classList.toggle('agenda-mode-active', !isWeeklyMode);
            agendaDailyCard.classList.toggle('agenda-mode-inactive', isWeeklyMode);
        }

        if (agendaWeeklyCard) {
            agendaWeeklyCard.classList.toggle('agenda-mode-active', isWeeklyMode);
            agendaWeeklyCard.classList.toggle('agenda-mode-inactive', !isWeeklyMode);
        }

        if (agendaDailyBody) {
            agendaDailyBody.classList.toggle('agenda-mode-disabled-body', isWeeklyMode);
        }

        if (agendaWeeklyBody) {
            agendaWeeklyBody.classList.toggle('agenda-mode-disabled-body', !isWeeklyMode);
        }
    };

    const initAgendaDataTable = () => {
        if (typeof $ === 'undefined' || !$.fn.DataTable || agendarDataTable) {
            return;
        }

        const table = $('#agendaRegistradaTable');

        if (!table.length) {
            return;
        }

        agendarDataTable = table.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: config.agendaDataUrl,
                beforeSend: function () {
                    Swal.fire({
                        title: 'Cargando...',
                        text: 'Obteniendo datos de la agenda',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrio un problema al cargar los datos',
                    });
                },
                complete: function () {
                    Swal.close();
                },
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'fecha_registro', name: 'fecha' },
                { data: 'departamento', name: 'departamento' },
                { data: 'nombre_apellido', name: 'nombre_apellido' },
                { data: 'periodo', name: 'fecha_desde', orderable: false, searchable: false },
                { data: 'horario_trabajo', name: 'hora_desde', orderable: false, searchable: false },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false },
            ],
        });
    };

    const showAgendaActivitiesHistory = () => {
        if (agendaActivitiesHistoryWrapper) {
            agendaActivitiesHistoryWrapper.classList.remove('d-none');
        }
    };

    const hideAgendaActivitiesHistory = () => {
        if (agendaActivitiesHistoryWrapper) {
            agendaActivitiesHistoryWrapper.classList.add('d-none');
        }
    };

    const buildAgendaActivitiesDataTable = (tableSelector, tipo) => {
        if (typeof $ === 'undefined' || !$.fn.DataTable) {
            return null;
        }

        const table = $(tableSelector);

        if (!table.length) {
            return null;
        }

        return table.DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            lengthChange: false,
            pageLength: 10,
            info: false,
            ordering: false,
            ajax: {
                url: config.activityDataUrl,
                data: function (d) {
                    d.agenda_id = currentAgendaId || '';
                    d.tipo = tipo;
                },
            },
            language: {
                emptyTable: tipo === 'D'
                    ? 'No hay actividades diarias registradas.'
                    : 'No hay actividades semanales registradas.',
                zeroRecords: tipo === 'D'
                    ? 'No hay actividades diarias registradas.'
                    : 'No hay actividades semanales registradas.',
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'actividad', name: 'actividad', orderable: false, searchable: false },
                { data: 'ejecucion', name: 'ejecucion', orderable: false, searchable: false },
                { data: 'equipo', name: 'equipo', orderable: false, searchable: false },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false },
            ],
        });
    };

    const initAgendaActivitiesDataTables = () => {
        if (!agendaDailyActivitiesDataTable && agendaDailyActivitiesTableElement) {
            agendaDailyActivitiesDataTable = buildAgendaActivitiesDataTable('#agendaDailyActivitiesTable', 'D');
        }

        if (!agendaWeeklyActivitiesDataTable && agendaWeeklyActivitiesTableElement) {
            agendaWeeklyActivitiesDataTable = buildAgendaActivitiesDataTable('#agendaWeeklyActivitiesTable', 'S');
        }
    };

    const reloadAgendaActivitiesDataTables = () => {
        initAgendaActivitiesDataTables();

        if (agendaDailyActivitiesDataTable) {
            agendaDailyActivitiesDataTable.ajax.reload(null, false);
        }

        if (agendaWeeklyActivitiesDataTable) {
            agendaWeeklyActivitiesDataTable.ajax.reload(null, false);
        }
    };

    const syncEditActivityWeekEndRange = () => {
        if (!editActivityWeekStartField || !editActivityWeekEndField) {
            return;
        }

        const weekStartMin = currentAgendaContext?.fechaDesde || '';
        const weekEndMax = currentAgendaContext?.fechaHasta || '';
        let weekStartValue = getIsoValue(editActivityWeekStartField) || weekStartMin;

        editActivityWeekStartField.min = weekStartMin;
        editActivityWeekStartField.max = weekEndMax;
        applyDatepickerRange(editActivityWeekStartField, {
            minDate: parseIsoDate(weekStartMin),
            maxDate: parseIsoDate(weekEndMax),
        });

        if (weekStartMin && weekStartValue && weekStartValue < weekStartMin) {
            setDatepickerValue(editActivityWeekStartField, weekStartMin);
            weekStartValue = weekStartMin;
        }

        if (weekEndMax && weekStartValue && weekStartValue > weekEndMax) {
            setDatepickerValue(editActivityWeekStartField, weekEndMax);
            weekStartValue = weekEndMax;
        }

        editActivityWeekEndField.min = weekStartValue || '';
        editActivityWeekEndField.max = weekEndMax;
        applyDatepickerRange(editActivityWeekEndField, {
            minDate: parseIsoDate(weekStartValue),
            maxDate: parseIsoDate(weekEndMax),
        });

        const weekEndValue = getIsoValue(editActivityWeekEndField);

        if (weekEndValue && editActivityWeekEndField.min && weekEndValue < editActivityWeekEndField.min) {
            setDatepickerValue(editActivityWeekEndField, editActivityWeekEndField.min);
        }

        if (weekEndValue && editActivityWeekEndField.max && weekEndValue > editActivityWeekEndField.max) {
            setDatepickerValue(editActivityWeekEndField, editActivityWeekEndField.max);
        }
    };

    const resetEditActivityModal = () => {
        if (editActivityForm) {
            editActivityForm.reset();
        }

        if (editActivityIdField) {
            editActivityIdField.value = '';
        }

        if (editActivityAgendaIdField) {
            editActivityAgendaIdField.value = '';
        }

        if (editActivityTypeField) {
            editActivityTypeField.value = '';
        }

        setDatepickerValue(editActivityWeekStartField, '');
        setDatepickerValue(editActivityWeekEndField, '');

        if (editActivityModalLabel) {
            editActivityModalLabel.textContent = 'Editar actividad';
        }

        if (editActivityModalSubtitle) {
            editActivityModalSubtitle.textContent = 'Actualiza la actividad seleccionada.';
        }

        if (editActivityDailyFields) {
            editActivityDailyFields.classList.remove('d-none');
        }

        if (editActivityWeeklyFields) {
            editActivityWeeklyFields.classList.add('d-none');
        }
    };

    const syncEditActivityMode = (tipo) => {
        const isWeekly = tipo === 'S';

        if (editActivityDailyFields) {
            editActivityDailyFields.classList.toggle('d-none', isWeekly);
        }

        if (editActivityWeeklyFields) {
            editActivityWeeklyFields.classList.toggle('d-none', !isWeekly);
        }

        if (editActivityModalLabel) {
            editActivityModalLabel.textContent = isWeekly
                ? 'Editar actividad semanal'
                : 'Editar actividad diaria';
        }

        if (editActivityModalSubtitle) {
            editActivityModalSubtitle.textContent = isWeekly
                ? 'Actualiza la actividad semanal seleccionada.'
                : 'Actualiza la actividad diaria seleccionada.';
        }

        if (isWeekly) {
            syncEditActivityWeekEndRange();
        }
    };

    const fetchActivityById = async (activityId) => {
        const response = await fetch(config.activityShowUrl.replace('__ID__', activityId), {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const responseData = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(responseData.message || 'No se pudo cargar la actividad.');
        }

        return responseData;
    };

    const fillEditActivityModal = (actividad) => {
        resetEditActivityModal();

        if (editActivityIdField) {
            editActivityIdField.value = String(actividad.id || '');
        }

        if (editActivityAgendaIdField) {
            editActivityAgendaIdField.value = String(actividad.agenda_id || currentAgendaId || '');
        }

        if (editActivityTypeField) {
            editActivityTypeField.value = actividad.tipo_actividad || '';
        }

        if (editActivityNameField) {
            editActivityNameField.value = actividad.actividad || '';
        }

        if (editActivityDepartmentField) {
            editActivityDepartmentField.value = String(actividad.departamento_id || '');
        }

        syncEditActivityMode(actividad.tipo_actividad || 'D');

        if ((actividad.tipo_actividad || 'D') === 'S') {
            setDatepickerValue(editActivityWeekStartField, actividad.fecha_del || '');
            setDatepickerValue(editActivityWeekEndField, actividad.fecha_hasta || '');
            syncEditActivityWeekEndRange();
        } else {
            const [fromHour = '', fromMinute = ''] = (actividad.hora_desde_actividad || '').split(':');
            const [toHour = '', toMinute = ''] = (actividad.hora_hasta_actividad || '').split(':');

            if (editActivityDailyFromHourField) {
                editActivityDailyFromHourField.value = fromHour;
            }

            if (editActivityDailyFromMinuteField) {
                editActivityDailyFromMinuteField.value = fromMinute;
            }

            if (editActivityDailyToHourField) {
                editActivityDailyToHourField.value = toHour;
            }

            if (editActivityDailyToMinuteField) {
                editActivityDailyToMinuteField.value = toMinute;
            }
        }
    };

    const buildEditActivityPayload = () => {
        const tipo = editActivityTypeField?.value || 'D';
        const horaDesde = `${editActivityDailyFromHourField?.value || ''}:${editActivityDailyFromMinuteField?.value || ''}`;
        const horaHasta = `${editActivityDailyToHourField?.value || ''}:${editActivityDailyToMinuteField?.value || ''}`;

        return {
            agenda_id: Number(editActivityAgendaIdField?.value || currentAgendaId || 0),
            actividad: editActivityNameField?.value?.trim() || '',
            departamento_id: editActivityDepartmentField?.value || '',
            tipo_actividad: tipo,
            fecha_del: tipo === 'S' ? getIsoValue(editActivityWeekStartField) : null,
            fecha_hasta: tipo === 'S' ? getIsoValue(editActivityWeekEndField) : null,
            hora_desde_actividad: tipo === 'D' ? horaDesde : null,
            hora_hasta_actividad: tipo === 'D' ? horaHasta : null,
        };
    };

    const submitActivityUpdate = async (activityId, payload) => {
        const response = await fetch(config.activityUpdateUrl.replace('__ID__', activityId), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        });

        const responseData = await response.json().catch(() => ({}));

        if (!response.ok) {
            if (response.status === 422 && responseData.errors) {
                const firstMessage = Object.values(responseData.errors).flat()[0];
                throw new Error(firstMessage || 'No se pudo actualizar la actividad.');
            }

            throw new Error(responseData.message || 'No se pudo actualizar la actividad.');
        }

        return responseData;
    };

    const submitActivityDelete = async (activityId) => {
        const response = await fetch(config.activityDestroyUrl.replace('__ID__', activityId), {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const responseData = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(responseData.message || 'No se pudo eliminar la actividad.');
        }

        return responseData;
    };

    const validateAgendaForm = () => {
        return window.WizardValidationUtils.validateFields(agendaHeaderFields, formValidationConfig);
    };

    const submitAgendaHeader = async () => {
        const formData = new FormData(form);

        const response = await fetch(form.action, {
            method: form.method || 'POST',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: (() => {
                formData.set('fecha', getIsoValue(document.getElementById('fecha')));
                formData.set('fecha_desde', getIsoValue(periodoDelField));
                formData.set('fecha_hasta', getIsoValue(periodoAlField));

                return formData;
            })(),
        });

        const responseData = await response.json().catch(() => ({}));

        if (!response.ok) {
            if (response.status === 422 && responseData.errors) {
                const firstMessage = Object.values(responseData.errors).flat()[0];
                throw new Error(firstMessage || 'No se pudo guardar la agenda.');
            }

            throw new Error(responseData.message || 'No se pudo guardar la agenda.');
        }

        return responseData;
    };

    const syncTimePairValidation = (fieldNames) => {
        const fields = fieldNames
            .map((name) => form.querySelector(`[name="${name}"]`))
            .filter(Boolean);

        if (fields.length < 2) {
            return;
        }

        const revalidatePair = () => {
            fields.forEach((field) => {
                if (field.value !== '' || field.classList.contains('is-invalid')) {
                    window.WizardValidationUtils.validateField(field, formValidationConfig);
                }
            });
        };

        fields.forEach((field) => {
            field.addEventListener('change', revalidatePair);
            field.addEventListener('blur', revalidatePair);
        });
    };

    const resetAgendaState = () => {
        if (isLoadingAgendaRecord) {
            return;
        }

        agendaReady = false;
        currentAgendaId = null;
        currentAgendaContext = null;
        hideWeeklyAgendaForm();
        hideAgendaActivitiesHistory();
    };

    const getActivityPayload = () => {
        const selectedMode = activityModeInputs.find((input) => input.checked)?.value || 'daily';
        const isWeeklyMode = selectedMode === 'weekly';
        const horaDesde = `${agendaDailyFromHourField?.value || ''}:${agendaDailyFromMinuteField?.value || ''}`;
        const horaHasta = `${agendaDailyToHourField?.value || ''}:${agendaDailyToMinuteField?.value || ''}`;

        return {
            agenda_id: currentAgendaId,
            actividad: agendaActivityNameField?.value?.trim() || '',
            departamento_id: agendaEquipoField?.value || '',
            tipo_actividad: isWeeklyMode ? 'S' : 'D',
            fecha_del: isWeeklyMode ? getIsoValue(agendaWeekStartField) : null,
            fecha_hasta: isWeeklyMode ? getIsoValue(agendaWeekEndField) : null,
            hora_desde_actividad: isWeeklyMode ? null : horaDesde,
            hora_hasta_actividad: isWeeklyMode ? null : horaHasta,
        };
    };

    const validateActivityPayload = (payload) => {
        if (!payload.agenda_id) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Primero debes registrar o seleccionar una agenda.',
            });

            return false;
        }

        if (!payload.actividad) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La actividad es obligatoria.',
            });

            return false;
        }

        if (!payload.departamento_id) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debe seleccionar un equipo.',
            });

            return false;
        }

        if (payload.tipo_actividad === 'S') {
            const context = currentAgendaContext || getAgendaContextFromForm();

            if (!payload.fecha_del || !payload.fecha_hasta) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Debe completar la fecha desde y la fecha hasta.',
                });

                return false;
            }

            if (payload.fecha_hasta < payload.fecha_del) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'La fecha hasta debe ser mayor o igual a la fecha desde.',
                });

                return false;
            }

            if (context.fechaDesde && payload.fecha_del < context.fechaDesde) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'La fecha desde de la actividad no puede ser menor al período de la agenda principal.',
                });

                return false;
            }

            if (context.fechaHasta && payload.fecha_hasta > context.fechaHasta) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'La fecha hasta de la actividad no puede ser mayor al período de la agenda principal.',
                });

                return false;
            }

            if (!isSameMonth(payload.fecha_del, payload.fecha_hasta)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'La fecha hasta debe pertenecer al mismo mes que la fecha desde.',
                });

                return false;
            }
        }

        if (payload.tipo_actividad === 'D') {
            const context = currentAgendaContext || getAgendaContextFromForm();

            if (payload.hora_desde_actividad.length !== 5 || payload.hora_hasta_actividad.length !== 5) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Debe completar las horas de la actividad diaria.',
                });

                return false;
            }

            if (context.horaDesde && payload.hora_desde_actividad < context.horaDesde) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'La hora inicial de la actividad no puede ser menor al horario de la agenda principal.',
                });

                return false;
            }

            if (context.horaHasta && payload.hora_hasta_actividad > context.horaHasta) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'La hora final de la actividad no puede ser mayor al horario de la agenda principal.',
                });

                return false;
            }

            if (payload.hora_hasta_actividad <= payload.hora_desde_actividad) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'La hora final debe ser mayor a la hora inicial.',
                });

                return false;
            }
        }

        return true;
    };

    const submitAgendaActivity = async (payload) => {
        const response = await fetch(config.activityStoreUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        });

        const responseData = await response.json().catch(() => ({}));

        if (!response.ok) {
            if (response.status === 422 && responseData.errors) {
                const firstMessage = Object.values(responseData.errors).flat()[0];
                throw new Error(firstMessage || 'No se pudo registrar la actividad.');
            }

            throw new Error(responseData.message || 'No se pudo registrar la actividad.');
        }

        return responseData;
    };

    const clearActivityForm = () => {
        if (agendaActivityNameField) {
            agendaActivityNameField.value = '';
        }

        if (agendaEquipoField) {
            agendaEquipoField.value = departamentoField?.value || '';
        }

        if (agendaWeekStartField) {
            $(agendaWeekStartField).datepicker('setDate', parseIsoDate(getIsoValue(periodoDelField)));
        }

        if (agendaWeekEndField) {
            $(agendaWeekEndField).datepicker('setDate', parseIsoDate(getIsoValue(periodoAlField)));
        }

        if (agendaDailyFromHourField) {
            agendaDailyFromHourField.value = form.querySelector('[name="hora_inicio_hora"]')?.value || '';
        }

        if (agendaDailyFromMinuteField) {
            agendaDailyFromMinuteField.value = form.querySelector('[name="hora_inicio_minuto"]')?.value || '';
        }

        if (agendaDailyToHourField) {
            agendaDailyToHourField.value = form.querySelector('[name="hora_fin_hora"]')?.value || '';
        }

        if (agendaDailyToMinuteField) {
            agendaDailyToMinuteField.value = form.querySelector('[name="hora_fin_minuto"]')?.value || '';
        }
    };

    const clearAgendaHeaderForm = () => {
        form.reset();

        setDatepickerValue(fechaField, '');
        setDatepickerValue(periodoDelField, '');
        setDatepickerValue(periodoAlField, '');
        setDatepickerValue(agendaWeekStartField, '');
        setDatepickerValue(agendaWeekEndField, '');

        if (departamentoField) {
            departamentoField.value = '';
        }

        if (solicitanteField) {
            solicitanteField.innerHTML = '<option value="">Seleccione un departamento primero</option>';
            solicitanteField.value = '';
        }

        if (cargoVisualField) {
            cargoVisualField.value = '';
        }

        [
            form.querySelector('[name="hora_inicio_hora"]'),
            form.querySelector('[name="hora_inicio_minuto"]'),
            form.querySelector('[name="hora_fin_hora"]'),
            form.querySelector('[name="hora_fin_minuto"]'),
        ].forEach((field) => {
            if (field) {
                field.value = '';
            }
        });

        if (periodoDelField) {
            periodoDelField.min = config.todayString || '';
            periodoDelField.max = '';
            applyDatepickerRange(periodoDelField, {
                minDate: parseIsoDate(config.todayString || ''),
                maxDate: null,
            });
        }

        if (periodoAlField) {
            periodoAlField.min = '';
            periodoAlField.max = '';
            applyDatepickerRange(periodoAlField, {
                minDate: null,
                maxDate: null,
            });
        }

        if (agendaWeekStartField) {
            agendaWeekStartField.min = '';
            agendaWeekStartField.max = '';
            applyDatepickerRange(agendaWeekStartField, {
                minDate: null,
                maxDate: null,
            });
        }

        if (agendaWeekEndField) {
            agendaWeekEndField.min = '';
            agendaWeekEndField.max = '';
            applyDatepickerRange(agendaWeekEndField, {
                minDate: null,
                maxDate: null,
            });
        }

        agendaHeaderFields.forEach((field) => {
            if (window.WizardValidationUtils?.clearFieldError) {
                window.WizardValidationUtils.clearFieldError(field);
            }
        });

        clearActivityForm();
        resetAgendaState();
        syncActivityMode();
    };

    const fetchActivitySuggestions = async (term) => {
        const url = new URL(config.activityAutocompleteUrl, window.location.origin);
        url.searchParams.set('q', term);

        const response = await fetch(url.toString(), {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error('No se pudieron cargar las sugerencias.');
        }

        return response.json();
    };

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
            firstDay: 1,
        });
    }

    initializeDatepicker(document.getElementById('fecha'));
    initializeDatepicker(periodoDelField, {
        minDate: parseIsoDate(config.todayString || getIsoValue(periodoDelField) || ''),
    });
    initializeDatepicker(periodoAlField);
    initializeDatepicker(agendaWeekStartField);
    initializeDatepicker(agendaWeekEndField);
    initializeDatepicker(editActivityWeekStartField);
    initializeDatepicker(editActivityWeekEndField);

    if (agendaPreviewWrapper) {
        agendaPreviewWrapper.classList.remove('d-none');
    }

    syncPeriodoAlRange();
    syncActivityMode();
    initAgendaDataTable();
    window.WizardValidationUtils.attachFieldEvents(form, formValidationConfig);
    syncTimePairValidation(['hora_inicio_hora', 'hora_inicio_minuto']);
    syncTimePairValidation(['hora_fin_hora', 'hora_fin_minuto']);

    if (periodoDelField) {
        periodoDelField.addEventListener('change', syncPeriodoAlRange);
        periodoDelField.addEventListener('input', syncPeriodoAlRange);
    }

    if (agendaWeekStartField) {
        agendaWeekStartField.addEventListener('change', syncAgendaWeekEndRange);
        agendaWeekStartField.addEventListener('input', syncAgendaWeekEndRange);
    }

    if (editActivityWeekStartField) {
        editActivityWeekStartField.addEventListener('change', syncEditActivityWeekEndRange);
        editActivityWeekStartField.addEventListener('input', syncEditActivityWeekEndRange);
    }

    activityModeInputs.forEach((input) => {
        input.addEventListener('change', syncActivityMode);
    });

    agendaHeaderFields.forEach((field) => {
        field.addEventListener('input', resetAgendaState);
        field.addEventListener('change', resetAgendaState);
    });

    if (agendaValidateButton) {
        agendaValidateButton.addEventListener('click', async function (event) {
            event.preventDefault();

            if (isSubmittingAgenda) {
                return;
            }

            if (!validateAgendaForm()) {
                resetAgendaState();
                return;
            }

            isSubmittingAgenda = true;
            agendaValidateButton.disabled = true;

            Swal.fire({
                title: 'Guardando...',
                text: 'Registrando encabezado de agenda',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            try {
                const result = await submitAgendaHeader();

                agendaReady = true;
                currentAgendaId = result.agenda_id || null;
                currentAgendaContext = getAgendaContextFromForm();

                if (agendaPreviewWrapper) {
                    agendaPreviewWrapper.classList.remove('d-none');
                }

                hideWeeklyAgendaForm();
                initAgendaDataTable();

                if (agendarDataTable) {
                    agendarDataTable.ajax.reload(function () {
                        Swal.fire({
                            icon: 'success',
                            title: 'Registro guardado',
                            text: result.message || 'La agenda se registró correctamente.',
                        }).then(() => {
                            clearAgendaHeaderForm();
                        });
                    }, false);
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Registro guardado',
                        text: result.message || 'La agenda se registró correctamente.',
                    }).then(() => {
                        clearAgendaHeaderForm();
                    });
                }
            } catch (error) {
                resetAgendaState();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'No se pudo registrar la agenda.',
                });
            } finally {
                isSubmittingAgenda = false;
                agendaValidateButton.disabled = false;
            }
        });
    }

    if (agendaTable) {
        agendaTable.addEventListener('click', async function (event) {
            const trigger = event.target.closest('button, a');

            if (!trigger) {
                return;
            }

            const triggerText = (trigger.textContent || '').trim().toLowerCase();
            const isEditAction =
                trigger.matches('[data-action="edit-agenda"], [data-agenda-edit], .agenda-edit-trigger, .btn-editar-agenda') ||
                triggerText.includes('editar');
            const isDeleteAction =
                trigger.matches('[data-action="delete-agenda"], .btn-eliminar-agenda');

            const agendaId = trigger.dataset.id || currentAgendaId;

            if (isDeleteAction) {
                event.preventDefault();

                if (!agendaId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo identificar la agenda seleccionada.',
                    });
                    return;
                }

                const confirmation = await Swal.fire({
                    icon: 'warning',
                    title: 'Eliminar agenda',
                    text: 'Desea eliminar este registro de agenda?',
                    showCancelButton: true,
                    confirmButtonText: 'Si, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#d33',
                });

                if (!confirmation.isConfirmed) {
                    return;
                }

                Swal.fire({
                    title: 'Eliminando...',
                    text: 'Quitando la agenda seleccionada',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });

                try {
                    const result = await submitAgendaDelete(agendaId);
                    const showDeleteSuccess = () => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Registro eliminado',
                            text: result.message || 'La agenda se elimino correctamente.',
                        });
                    };

                    if (String(currentAgendaId || '') === String(agendaId)) {
                        latestAgendaRequestToken += 1;
                        clearAgendaHeaderForm();
                    }

                    if (agendarDataTable) {
                        agendarDataTable.ajax.reload(showDeleteSuccess, false);
                    } else {
                        showDeleteSuccess();
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'No se pudo eliminar la agenda.',
                    });
                }

                return;
            }

            if (!isEditAction) {
                return;
            }

            event.preventDefault();

            if (!agendaId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo identificar la agenda seleccionada.',
                });
                return;
            }

            Swal.fire({
                title: 'Cargando...',
                text: 'Recuperando la agenda seleccionada',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            const requestToken = ++latestAgendaRequestToken;

            try {
                const agenda = await fetchAgendaById(agendaId);

                if (requestToken !== latestAgendaRequestToken) {
                    return;
                }

                await populateAgendaForm(agenda);

                if (requestToken !== latestAgendaRequestToken) {
                    return;
                }

                showWeeklyAgendaForm();
                showAgendaActivitiesHistory();
                reloadAgendaActivitiesDataTables();
                Swal.close();
            } catch (error) {
                if (requestToken !== latestAgendaRequestToken) {
                    return;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'No se pudo cargar la agenda seleccionada.',
                });
            }
        });
    }

    if (agendaPreviewButton) {
        agendaPreviewButton.addEventListener('click', function () {
            if (!currentAgendaId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Agenda no seleccionada',
                    text: 'Primero debe seleccionar una agenda para previsualizarla.',
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

                agendaPreviewFrame.src = `${config.agendaPreviewUrl.replace('__ID__', currentAgendaId)}?t=${Date.now()}`;
            }

            agendaPreviewModal?.show();
        });
    }

    if (agendaPreviewModalElement && agendaPreviewFrame) {
        agendaPreviewModalElement.addEventListener('hidden.bs.modal', function () {
            agendaPreviewFrame.src = 'about:blank';
        });
    }

    if (agendaSendButton) {
        agendaSendButton.addEventListener('click', async function () {
            if (!currentAgendaId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Agenda no seleccionada',
                    text: 'Primero debe seleccionar una agenda para enviarla.',
                });
                return;
            }

            const confirmation = await Swal.fire({
                icon: 'warning',
                title: 'Enviar agenda',
                text: 'Desea enviar esta agenda?',
                showCancelButton: true,
                confirmButtonText: 'Si, enviar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#198754',
            });

            if (!confirmation.isConfirmed) {
                return;
            }

            Swal.fire({
                title: 'Enviando...',
                text: 'Actualizando el estado de la agenda',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            try {
                const agendaId = currentAgendaId;
                const result = await submitAgendaSend(agendaId);
                const showSendSuccess = () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Agenda enviada',
                        text: result.message || 'La agenda se envio correctamente.',
                    });
                };

                if (String(currentAgendaId || '') === String(agendaId)) {
                    latestAgendaRequestToken += 1;
                    clearAgendaHeaderForm();
                }

                if (agendarDataTable) {
                    agendarDataTable.ajax.reload(showSendSuccess, false);
                } else {
                    showSendSuccess();
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'No se pudo enviar la agenda.',
                });
            }
        });
    }

    const handleActivityTableActions = async (event) => {
        const trigger = event.target.closest('button, a');

        if (!trigger) {
            return;
        }

        const activityId = trigger.dataset.id;

        if (!activityId) {
            return;
        }

        if (trigger.matches('[data-action="edit-activity"], .btn-editar-actividad')) {
            Swal.fire({
                title: 'Cargando...',
                text: 'Recuperando la actividad seleccionada',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            try {
                const actividad = await fetchActivityById(activityId);
                fillEditActivityModal(actividad);
                Swal.close();
                editActivityModal?.show();
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'No se pudo cargar la actividad seleccionada.',
                });
            }

            return;
        }

        if (trigger.matches('[data-action="delete-activity"], .btn-eliminar-actividad')) {
            const confirmation = await Swal.fire({
                icon: 'warning',
                title: 'Eliminar actividad',
                text: '¿Desea eliminar esta actividad?',
                showCancelButton: true,
                confirmButtonText: 'Si, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#d33',
            });

            if (!confirmation.isConfirmed) {
                return;
            }

            Swal.fire({
                title: 'Eliminando...',
                text: 'Quitando la actividad seleccionada',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            try {
                const result = await submitActivityDelete(activityId);

                if (editActivityIdField?.value === String(activityId)) {
                    editActivityModal?.hide();
                }

                reloadAgendaActivitiesDataTables();

                Swal.fire({
                    icon: 'success',
                    title: 'Registro eliminado',
                    text: result.message || 'La actividad se eliminó correctamente.',
                });
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'No se pudo eliminar la actividad.',
                });
            }
        }
    };

    [agendaDailyActivitiesTableElement, agendaWeeklyActivitiesTableElement].forEach((tableElement) => {
        if (tableElement) {
            tableElement.addEventListener('click', handleActivityTableActions);
        }
    });

    if (editActivityForm) {
        editActivityForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            if (isSubmittingActivityEdit) {
                return;
            }

            const activityId = editActivityIdField?.value || '';

            if (!activityId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo identificar la actividad seleccionada.',
                });
                return;
            }

            const payload = buildEditActivityPayload();

            if (!validateActivityPayload(payload)) {
                return;
            }

            isSubmittingActivityEdit = true;

            Swal.fire({
                title: 'Actualizando...',
                text: 'Guardando cambios de la actividad',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            try {
                const result = await submitActivityUpdate(activityId, payload);
                editActivityModal?.hide();
                reloadAgendaActivitiesDataTables();

                Swal.fire({
                    icon: 'success',
                    title: 'Registro actualizado',
                    text: result.message || 'La actividad se actualizó correctamente.',
                });
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'No se pudo actualizar la actividad.',
                });
            } finally {
                isSubmittingActivityEdit = false;
            }
        });
    }

    if (editActivityModalElement) {
        editActivityModalElement.addEventListener('hidden.bs.modal', resetEditActivityModal);
    }

    if (agendaRegisterActivityButton) {
        agendaRegisterActivityButton.addEventListener('click', async function () {
            if (isSubmittingActivity) {
                return;
            }

            const payload = getActivityPayload();

            if (!validateActivityPayload(payload)) {
                return;
            }

            isSubmittingActivity = true;
            agendaRegisterActivityButton.disabled = true;

            Swal.fire({
                title: 'Guardando...',
                text: 'Registrando actividad',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            try {
                const result = await submitAgendaActivity(payload);

                clearActivityForm();
                showAgendaActivitiesHistory();
                reloadAgendaActivitiesDataTables();

                Swal.fire({
                    icon: 'success',
                    title: 'Registro guardado',
                    text: result.message || 'La actividad se registró correctamente.',
                });
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'No se pudo registrar la actividad.',
                });
            } finally {
                isSubmittingActivity = false;
                agendaRegisterActivityButton.disabled = false;
            }
        });
    }

    if (agendaActivityNameField && typeof $ !== 'undefined' && $.ui && $.ui.autocomplete) {
        $(agendaActivityNameField).autocomplete({
            minLength: 2,
            delay: 250,
            source: function (request, response) {
                clearTimeout(activityAutocompleteTimer);

                activityAutocompleteTimer = window.setTimeout(async () => {
                    try {
                        const suggestions = await fetchActivitySuggestions(request.term);
                        response(Array.isArray(suggestions) ? suggestions : []);
                    } catch (error) {
                        response([]);
                    }
                }, 0);
            },
            select: function (event, ui) {
                agendaActivityNameField.value = ui.item.value;
                return false;
            },
        });
    }
    const cargarUsuariosPorDepartamento = async (departamentoId, selectedUserId = null) => {
        if (!solicitanteField) {
            return [];
        }

        solicitanteField.innerHTML = '<option value="">Cargando usuarios...</option>';

        if (!departamentoId) {
            solicitanteField.innerHTML = '<option value="">Seleccione un departamento primero</option>';
            return [];
        }

        try {
            const url = config.usuariosPorDepartamentoUrl.replace('__ID__', departamentoId);
            const response = await fetch(url);
            const usuarios = await response.json();

            solicitanteField.innerHTML = '<option value="">Seleccione un usuario</option>';

            usuarios.forEach((usuario) => {
                const option = document.createElement('option');
                option.value = usuario.id;
                option.textContent = usuario.name;
                option.dataset.tipoPersonal = usuario.tipo_personal_nombre ?? '';
                solicitanteField.appendChild(option);
            });

            if (selectedUserId !== null && selectedUserId !== undefined) {
                solicitanteField.value = String(selectedUserId);
            }

            syncCargoVisualWithSelectedUser();
            return usuarios;
        } catch (error) {
            solicitanteField.innerHTML = '<option value="">Error al cargar usuarios</option>';
            return [];
        }
    };
    if (departamentoField) {
        departamentoField.addEventListener('change', function () {
            cargarUsuariosPorDepartamento(this.value);
            if(cargoVisualField) {
                cargoVisualField.value = '';
            }

            resetAgendaState();
        });
    }
    if (solicitanteField) {
        solicitanteField.addEventListener('change', function () {
            syncCargoVisualWithSelectedUser();

            resetAgendaState();
        });
    }
});
