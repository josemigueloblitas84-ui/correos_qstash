document.addEventListener('DOMContentLoaded', function () {
    const config = window.sedesConfig || {};
    const tableElement = document.getElementById('tablaSedes');
    const createForm = document.getElementById('formCrearSede');
    const editForm = document.getElementById('formEditarSede');
    const editIdField = document.getElementById('editar_id');

    if (!tableElement || typeof $ === 'undefined' || !$.fn.DataTable) {
        return;
    }

    const createModalElement = document.getElementById('modalCrearSede');
    const editModalElement = document.getElementById('modalEditarSede');
    const createModal = createModalElement ? bootstrap.Modal.getOrCreateInstance(createModalElement) : null;
    const editModal = editModalElement ? bootstrap.Modal.getOrCreateInstance(editModalElement) : null;

    const table = $('#tablaSedes').DataTable({
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
                        text: 'Registrando sede',
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
                        text: response.message || 'Sede creada correctamente.',
                    });
                },
                error: function (xhr) {
                    handleAjaxError(xhr, createForm, 'error_crear_', 'No se pudo crear la sede.');
                },
            });
        });
    }

    $('#tablaSedes').on('click', '.btn-editar', function () {
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
                    text: 'Obteniendo datos de la sede',
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
                handleAjaxError(xhr, editForm || createForm, 'error_editar_', 'No se pudo cargar la sede seleccionada.');
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
                        text: 'Guardando cambios de la sede',
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
                        text: response.message || 'Sede actualizada correctamente.',
                    });
                },
                error: function (xhr) {
                    handleAjaxError(xhr, editForm, 'error_editar_', 'No se pudo actualizar la sede.');
                },
            });
        });
    }

    $('#tablaSedes').on('click', '.btn-eliminar', function () {
        const id = this.getAttribute('data-id');

        if (!id) {
            return;
        }

        Swal.fire({
            icon: 'warning',
            title: 'Eliminar sede',
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
                        text: 'Procesando la eliminacion de la sede',
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
                        text: response.message || 'Sede eliminada correctamente.',
                    });
                },
                error: function (xhr) {
                    handleAjaxError(xhr, editForm || createForm, 'error_editar_', 'No se pudo eliminar la sede.');
                },
            });
        });
    });
});
