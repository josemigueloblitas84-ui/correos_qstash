document.addEventListener('DOMContentLoaded', function () {
    const config = window.institucionesConfig || {};
    const tableElement = document.getElementById('tablaInstituciones');
    const createForm = document.getElementById('formCrearInstitucion');
    const editForm = document.getElementById('formEditarInstitucion');
    const assignmentForm = document.getElementById('formSedesInstitucion');
    const editIdField = document.getElementById('editar_id');
    const assignmentIdField = document.getElementById('sedes_institucion_id');
    const assignmentNameField = document.getElementById('sedes_institucion_nombre');
    const assignmentList = document.getElementById('listaSedesInstitucion');

    if (!tableElement || typeof $ === 'undefined' || !$.fn.DataTable) {
        return;
    }

    const createModalElement = document.getElementById('modalCrearInstitucion');
    const editModalElement = document.getElementById('modalEditarInstitucion');
    const assignmentModalElement = document.getElementById('modalSedesInstitucion');
    const createModal = createModalElement ? bootstrap.Modal.getOrCreateInstance(createModalElement) : null;
    const editModal = editModalElement ? bootstrap.Modal.getOrCreateInstance(editModalElement) : null;
    const assignmentModal = assignmentModalElement ? bootstrap.Modal.getOrCreateInstance(assignmentModalElement) : null;

    const table = $('#tablaInstituciones').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: {
            url: config.dataUrl,
            type: 'GET',
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'nombre', name: 'nombre' },
            { data: 'sedes', name: 'sedes', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false },
        ],
        language: {
            url: '/assets/datatables/i18n/es-ES.json',
        },
        pageLength: 10,
        lengthChange: true,
        searching: true,
        ordering: true,
        layout: {
            topStart: 'pageLength',
            topEnd: 'search',
            bottomStart: 'info',
            bottomEnd: 'paging',
        },
    });

    function buildUrl(template, id) {
        return template.replace('__ID__', encodeURIComponent(String(id)));
    }

    function clearValidation(form, prefix) {
        form.querySelectorAll('.is-invalid').forEach(function (input) {
            input.classList.remove('is-invalid');
        });

        form.querySelectorAll('.invalid-feedback').forEach(function (feedback) {
            if (feedback.id.startsWith(prefix)) {
                feedback.textContent = '';
            }
        });
    }

    function renderValidationErrors(form, prefix, errors) {
        clearValidation(form, prefix);

        Object.keys(errors || {}).forEach(function (field) {
            const normalizedField = field.includes('.') ? field.split('.')[0] : field;
            const input = form.querySelector('[name="' + field + '"]') || form.querySelector('[name="' + normalizedField + '[]"]') || form.querySelector('[name="' + normalizedField + '"]');
            const errorContainer = document.getElementById(prefix + field) || document.getElementById(prefix + normalizedField);

            if (input) {
                input.classList.add('is-invalid');
            }

            if (errorContainer) {
                errorContainer.textContent = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
            }
        });
    }

    function handleAjaxError(xhr, form, prefix, genericMessage) {
        Swal.close();

        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
            renderValidationErrors(form, prefix, xhr.responseJSON.errors);
            return;
        }

        const message = xhr.responseJSON && xhr.responseJSON.message
            ? xhr.responseJSON.message
            : genericMessage;

        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
        });
    }

    function resetCreateForm() {
        if (!createForm) {
            return;
        }

        createForm.reset();
        clearValidation(createForm, 'error_crear_');
    }

    function resetEditForm() {
        if (!editForm) {
            return;
        }

        editForm.reset();
        clearValidation(editForm, 'error_editar_');

        if (editIdField) {
            editIdField.value = '';
        }
    }

    function resetAssignmentForm() {
        if (!assignmentForm) {
            return;
        }

        assignmentForm.reset();
        clearValidation(assignmentForm, 'error_asignar_');

        if (assignmentIdField) {
            assignmentIdField.value = '';
        }

        if (assignmentNameField) {
            assignmentNameField.textContent = '-';
        }

        if (assignmentList) {
            assignmentList.innerHTML = '';
        }
    }

    function renderSedeOptions(sedes) {
        if (!assignmentList) {
            return;
        }

        assignmentList.innerHTML = '';

        if (!Array.isArray(sedes) || sedes.length === 0) {
            assignmentList.innerHTML = '<div class="alert alert-secondary mb-0">No hay sedes registradas.</div>';
            return;
        }

        sedes.forEach(function (sede) {
            const wrapper = document.createElement('label');
            wrapper.className = 'form-check d-flex align-items-center gap-2 border rounded px-3 py-2';

            const input = document.createElement('input');
            input.type = 'checkbox';
            input.className = 'form-check-input mt-0';
            input.name = 'sedes[]';
            input.value = sede.id;
            input.checked = Boolean(sede.checked);

            const text = document.createElement('span');
            text.textContent = sede.nombre || '';

            wrapper.appendChild(input);
            wrapper.appendChild(text);
            assignmentList.appendChild(wrapper);
        });
    }

    if (createModalElement) {
        createModalElement.addEventListener('hidden.bs.modal', resetCreateForm);
    }

    if (editModalElement) {
        editModalElement.addEventListener('hidden.bs.modal', resetEditForm);
    }

    if (assignmentModalElement) {
        assignmentModalElement.addEventListener('hidden.bs.modal', resetAssignmentForm);
    }

    if (createForm) {
        createForm.addEventListener('submit', function (event) {
            event.preventDefault();
            clearValidation(createForm, 'error_crear_');

            $.ajax({
                url: config.storeUrl,
                type: 'POST',
                data: $(createForm).serialize(),
                headers: {
                    'X-CSRF-TOKEN': config.csrfToken,
                },
                beforeSend: function () {
                    Swal.fire({
                        title: 'Guardando...',
                        text: 'Registrando institucion',
                        allowOutsideClick: false,
                        didOpen: function () {
                            Swal.showLoading();
                        },
                    });
                },
                success: function (response) {
                    Swal.close();

                    if (createModal) {
                        createModal.hide();
                    }

                    table.ajax.reload(null, false);

                    Swal.fire({
                        icon: 'success',
                        title: 'Correcto',
                        text: response.message || 'Institucion creada correctamente.',
                    });
                },
                error: function (xhr) {
                    handleAjaxError(xhr, createForm, 'error_crear_', 'No se pudo crear la institucion.');
                },
            });
        });
    }

    $('#tablaInstituciones').on('click', '.btn-editar', function () {
        const id = this.getAttribute('data-id');

        if (!id) {
            return;
        }

        resetEditForm();

        $.ajax({
            url: buildUrl(config.showUrlTemplate, id),
            type: 'GET',
            beforeSend: function () {
                Swal.fire({
                    title: 'Cargando...',
                    text: 'Obteniendo datos de la institucion',
                    allowOutsideClick: false,
                    didOpen: function () {
                        Swal.showLoading();
                    },
                });
            },
            success: function (response) {
                Swal.close();

                const data = response.data || {};
                const nombreField = document.getElementById('editar_nombre');

                if (editIdField) {
                    editIdField.value = data.id || '';
                }

                if (nombreField) {
                    nombreField.value = data.nombre || '';
                }

                if (editModal) {
                    editModal.show();
                }
            },
            error: function (xhr) {
                handleAjaxError(xhr, editForm || createForm, 'error_editar_', 'No se pudo cargar la institucion seleccionada.');
            },
        });
    });

    if (editForm) {
        editForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const id = editIdField ? editIdField.value : '';
            if (!id) {
                return;
            }

            clearValidation(editForm, 'error_editar_');

            $.ajax({
                url: buildUrl(config.updateUrlTemplate, id),
                type: 'POST',
                data: $(editForm).serialize() + '&_method=PUT',
                headers: {
                    'X-CSRF-TOKEN': config.csrfToken,
                },
                beforeSend: function () {
                    Swal.fire({
                        title: 'Actualizando...',
                        text: 'Guardando cambios de la institucion',
                        allowOutsideClick: false,
                        didOpen: function () {
                            Swal.showLoading();
                        },
                    });
                },
                success: function (response) {
                    Swal.close();

                    if (editModal) {
                        editModal.hide();
                    }

                    table.ajax.reload(null, false);

                    Swal.fire({
                        icon: 'success',
                        title: 'Correcto',
                        text: response.message || 'Institucion actualizada correctamente.',
                    });
                },
                error: function (xhr) {
                    handleAjaxError(xhr, editForm, 'error_editar_', 'No se pudo actualizar la institucion.');
                },
            });
        });
    }

    $('#tablaInstituciones').on('click', '.btn-eliminar', function () {
        const id = this.getAttribute('data-id');

        if (!id) {
            return;
        }

        Swal.fire({
            icon: 'warning',
            title: 'Eliminar institucion',
            text: 'Esta accion no se puede deshacer.',
            showCancelButton: true,
            confirmButtonText: 'Si, eliminar',
            cancelButtonText: 'Cancelar',
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                url: buildUrl(config.destroyUrlTemplate, id),
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: config.csrfToken,
                },
                headers: {
                    'X-CSRF-TOKEN': config.csrfToken,
                },
                beforeSend: function () {
                    Swal.fire({
                        title: 'Eliminando...',
                        text: 'Procesando la eliminacion de la institucion',
                        allowOutsideClick: false,
                        didOpen: function () {
                            Swal.showLoading();
                        },
                    });
                },
                success: function (response) {
                    Swal.close();
                    table.ajax.reload(null, false);

                    Swal.fire({
                        icon: 'success',
                        title: 'Correcto',
                        text: response.message || 'Institucion eliminada correctamente.',
                    });
                },
                error: function (xhr) {
                    handleAjaxError(xhr, editForm || createForm, 'error_editar_', 'No se pudo eliminar la institucion.');
                },
            });
        });
    });

    $('#tablaInstituciones').on('click', '.btn-sedes', function () {
        const id = this.getAttribute('data-id');

        if (!id) {
            return;
        }

        resetAssignmentForm();

        $.ajax({
            url: buildUrl(config.sedesEditUrlTemplate, id),
            type: 'GET',
            beforeSend: function () {
                Swal.fire({
                    title: 'Cargando...',
                    text: 'Obteniendo sedes de la institucion',
                    allowOutsideClick: false,
                    didOpen: function () {
                        Swal.showLoading();
                    },
                });
            },
            success: function (response) {
                Swal.close();

                const data = response.data || {};
                const institucion = data.institucion || {};

                if (assignmentIdField) {
                    assignmentIdField.value = institucion.id || '';
                }

                if (assignmentNameField) {
                    assignmentNameField.textContent = institucion.nombre || '-';
                }

                renderSedeOptions(data.sedes || []);

                if (assignmentModal) {
                    assignmentModal.show();
                }
            },
            error: function (xhr) {
                handleAjaxError(xhr, assignmentForm || editForm || createForm, 'error_asignar_', 'No se pudo cargar la asignacion de sedes.');
            },
        });
    });

    if (assignmentForm) {
        assignmentForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const id = assignmentIdField ? assignmentIdField.value : '';
            if (!id) {
                return;
            }

            clearValidation(assignmentForm, 'error_asignar_');

            $.ajax({
                url: buildUrl(config.sedesUpdateUrlTemplate, id),
                type: 'POST',
                data: $(assignmentForm).serialize() + '&_method=PUT',
                headers: {
                    'X-CSRF-TOKEN': config.csrfToken,
                },
                beforeSend: function () {
                    Swal.fire({
                        title: 'Guardando...',
                        text: 'Actualizando sedes de la institucion',
                        allowOutsideClick: false,
                        didOpen: function () {
                            Swal.showLoading();
                        },
                    });
                },
                success: function (response) {
                    Swal.close();

                    if (assignmentModal) {
                        assignmentModal.hide();
                    }

                    table.ajax.reload(null, false);

                    Swal.fire({
                        icon: 'success',
                        title: 'Correcto',
                        text: response.message || 'Sedes asignadas correctamente.',
                    });
                },
                error: function (xhr) {
                    handleAjaxError(xhr, assignmentForm, 'error_asignar_', 'No se pudieron asignar las sedes.');
                },
            });
        });
    }
});
