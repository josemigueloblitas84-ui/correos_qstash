document.addEventListener('DOMContentLoaded', function () {
    const config = window.departamentosConfig || {};
    const tableElement = document.getElementById('tablaDepartamentos');
    const createForm = document.getElementById('formCrearDepartamento');
    const editForm = document.getElementById('formEditarDepartamento');
    const editIdField = document.getElementById('editar_id');

    if (!tableElement || typeof $ === 'undefined' || !$.fn.DataTable) {
        return;
    }

    const createModalElement = document.getElementById('modalCrearDepartamento');
    const editModalElement = document.getElementById('modalEditarDepartamento');
    const createModal = createModalElement ? bootstrap.Modal.getOrCreateInstance(createModalElement) : null;
    const editModal = editModalElement ? bootstrap.Modal.getOrCreateInstance(editModalElement) : null;

    const table = $('#tablaDepartamentos').DataTable({
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
            { data: 'nombre_depa', name: 'nombre_depa' },
            {
                data: 'estado_depa',
                name: 'estado_depa',
                render: function (data) {
                    const normalized = (data || '').toString().toLowerCase();
                    const badgeClass = normalized === 'activo'
                        ? 'departamento-badge departamento-badge-success'
                        : 'departamento-badge departamento-badge-warning';

                    return '<span class="' + badgeClass + '">' + escapeHtml(data || '') + '</span>';
                },
            },
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

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

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
            const input = form.querySelector('[name="' + field + '"]');
            const errorContainer = document.getElementById(prefix + field);

            if (input) {
                input.classList.add('is-invalid');
            }

            if (errorContainer) {
                errorContainer.textContent = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
            }
        });
    }

    function resetCreateForm() {
        if (!createForm) {
            return;
        }

        createForm.reset();

        const estadoField = document.getElementById('crear_estado_depa');
        if (estadoField) {
            estadoField.value = 'activo';
        }

        clearValidation(createForm, 'error_crear_');
    }

    function resetEditForm() {
        if (!editForm) {
            return;
        }

        editForm.reset();

        if (editIdField) {
            editIdField.value = '';
        }

        clearValidation(editForm, 'error_editar_');
    }

    function handleAjaxError(xhr, form, prefix, genericMessage) {
        Swal.close();

        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
            renderValidationErrors(form, prefix, xhr.responseJSON.errors);
            return;
        }

        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: genericMessage,
        });
    }

    if (createModalElement) {
        createModalElement.addEventListener('hidden.bs.modal', resetCreateForm);
    }

    if (editModalElement) {
        editModalElement.addEventListener('hidden.bs.modal', resetEditForm);
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
                        text: 'Registrando departamento',
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
                        text: response.message || 'Departamento creado correctamente.',
                    });
                },
                error: function (xhr) {
                    handleAjaxError(xhr, createForm, 'error_crear_', 'No se pudo crear el departamento.');
                },
            });
        });
    }

    $('#tablaDepartamentos').on('click', '.btn-editar', function () {
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
                    text: 'Obteniendo datos del departamento',
                    allowOutsideClick: false,
                    didOpen: function () {
                        Swal.showLoading();
                    },
                });
            },
            success: function (response) {
                Swal.close();

                const data = response.data || {};
                const nombreField = document.getElementById('editar_nombre_depa');
                const estadoField = document.getElementById('editar_estado_depa');

                if (editIdField) {
                    editIdField.value = data.id || '';
                }

                if (nombreField) {
                    nombreField.value = data.nombre_depa || '';
                }

                if (estadoField) {
                    estadoField.value = data.estado_depa || 'activo';
                }

                if (editModal) {
                    editModal.show();
                }
            },
            error: function () {
                Swal.close();

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar el departamento seleccionado.',
                });
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
                        text: 'Guardando cambios del departamento',
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
                        text: response.message || 'Departamento actualizado correctamente.',
                    });
                },
                error: function (xhr) {
                    handleAjaxError(xhr, editForm, 'error_editar_', 'No se pudo actualizar el departamento.');
                },
            });
        });
    }

    $('#tablaDepartamentos').on('click', '.btn-toggle-estado', function () {
        const id = this.getAttribute('data-id');
        const estadoActual = (this.getAttribute('data-estado') || '').toLowerCase();
        const activar = estadoActual !== 'activo';
        const titulo = activar ? 'Activar departamento' : 'Desactivar departamento';
        const descripcion = activar
            ? 'El departamento volvera a estar disponible para nuevos registros.'
            : 'El departamento quedara inactivo y dejara de estar disponible para nuevos registros.';
        const confirmText = activar ? 'Si, activar' : 'Si, desactivar';
        const progressText = activar ? 'Activando departamento' : 'Desactivando departamento';
        const successText = activar ? 'Departamento activado correctamente.' : 'Departamento desactivado correctamente.';

        if (!id) {
            return;
        }

        Swal.fire({
            icon: 'warning',
            title: titulo,
            text: descripcion,
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#5b63f6',
            cancelButtonColor: '#95a1b2',
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                url: buildUrl(config.toggleStatusUrlTemplate, id),
                type: 'POST',
                data: {
                    _method: 'PATCH',
                    _token: config.csrfToken,
                },
                headers: {
                    'X-CSRF-TOKEN': config.csrfToken,
                },
                beforeSend: function () {
                    Swal.fire({
                        title: 'Procesando...',
                        text: progressText,
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
                        text: response.message || successText,
                    });
                },
                error: function () {
                    Swal.close();

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: activar
                            ? 'No se pudo activar el departamento.'
                            : 'No se pudo desactivar el departamento.',
                    });
                },
            });
        });
    });
});
