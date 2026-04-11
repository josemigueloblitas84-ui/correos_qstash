document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('articuloWizardFormV2');

    if (!form) {
        return;
    }

    const config = window.formV2Config || {};
    const agendaValidateButton = form.querySelector('[data-action="validate-agenda"]');
    const agendaPreviewWrapper = document.getElementById('agendaPreviewWrapper');
    const agendaWeeklyWrapper = document.getElementById('agendaWeeklyWrapper');
    const agendaTable = document.getElementById('agendaRegistradaTable');
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
    const activityModeInputs = Array.from(form.querySelectorAll('input[name="agenda_activity_mode"]'));
    const agendaDailyCard = document.getElementById('agendaDailyCard');
    const agendaWeeklyCard = document.getElementById('agendaWeeklyCard');
    const agendaDailyBody = document.getElementById('agendaDailyBody');
    const agendaWeeklyBody = document.getElementById('agendaWeeklyBody');
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
    let agendaReady = false;
    let isSubmittingAgenda = false;

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
                return value >= todayString || 'El periodo inicial debe ser mayor o igual a la fecha actual.';
            },
            message: 'El periodo inicial es obligatorio.',
        },
        fecha_hasta: {
            label: 'periodo al',
            required: true,
            custom: (value) => {
                const periodoDel = form.querySelector('[name="fecha_desde"]')?.value;

                if (!periodoDel) {
                    return 'Primero debes seleccionar el periodo inicial.';
                }

                return value >= periodoDel || 'El periodo final debe ser mayor o igual al periodo inicial';
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

    const syncPeriodoAlMin = () => {
        if (!periodoDelField || !periodoAlField) {
            return;
        }

        const periodoDelValue = periodoDelField.value || periodoDelField.min;

        if (periodoDelValue) {
            periodoAlField.min = periodoDelValue;
        }
    };

    const fillWeeklyAgendaForm = () => {
        const periodoDel = form.querySelector('[name="fecha_desde"]')?.value || '';
        const periodoAl = form.querySelector('[name="fecha_hasta"]')?.value || '';
        const horaInicioHora = form.querySelector('[name="hora_inicio_hora"]')?.value || '';
        const horaInicioMinuto = form.querySelector('[name="hora_inicio_minuto"]')?.value || '';
        const horaFinHora = form.querySelector('[name="hora_fin_hora"]')?.value || '';
        const horaFinMinuto = form.querySelector('[name="hora_fin_minuto"]')?.value || '';
        const cargo = cargoVisualField?.value || '';

        if (agendaWeekStartField) {
            agendaWeekStartField.value = periodoDel;
        }

        if (agendaWeekEndField) {
            agendaWeekEndField.value = periodoAl;
        }

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
            body: formData,
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
        agendaReady = false;
        hideAgendaPreview();
        hideWeeklyAgendaForm();
    };

    syncPeriodoAlMin();
    syncActivityMode();
    window.WizardValidationUtils.attachFieldEvents(form, formValidationConfig);
    syncTimePairValidation(['hora_inicio_hora', 'hora_inicio_minuto']);
    syncTimePairValidation(['hora_fin_hora', 'hora_fin_minuto']);

    if (periodoDelField) {
        periodoDelField.addEventListener('change', syncPeriodoAlMin);
        periodoDelField.addEventListener('input', syncPeriodoAlMin);
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
                        });
                    }, false);
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Registro guardado',
                        text: result.message || 'La agenda se registró correctamente.',
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
        agendaTable.addEventListener('click', function (event) {
            const trigger = event.target.closest('button, a');

            if (!trigger) {
                return;
            }

            const triggerText = (trigger.textContent || '').trim().toLowerCase();
            const isEditAction =
                trigger.matches('[data-action="edit-agenda"], [data-agenda-edit], .agenda-edit-trigger, .btn-editar-agenda') ||
                triggerText.includes('editar');

            if (!isEditAction) {
                return;
            }

            event.preventDefault();

            if (!agendaReady && !validateAgendaForm()) {
                return;
            }

            showWeeklyAgendaForm();
        });
    }
    const cargarUsuariosPorDepartamento = async (departamentoId) => {
        if (!solicitanteField) {
            return;
        }

        solicitanteField.innerHTML = '<option value="">Cargando usuarios...</option>';

        if (!departamentoId) {
            solicitanteField.innerHTML = '<option value="">Seleccione un departamento primero</option>';
            return;
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
        } catch (error) {
            solicitanteField.innerHTML = '<option value="">Error al cargar usuarios</option>';
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
            const selectedOption = this.options[this.selectedIndex];
            const tipoPersonal = selectedOption?.dataset?.tipoPersonal || '';

            if (cargoVisualField) {
                cargoVisualField.value = tipoPersonal;
            }

            resetAgendaState();
        });
    }
});
