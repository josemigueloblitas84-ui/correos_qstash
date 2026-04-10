document.addEventListener('DOMContentLoaded', function () {
    const config = window.tiposPersonalConfig || {};
    const tableElement = document.getElementById('tablaTiposPersonal');
    const createForm = document.getElementById('formCrearTipoPersonal');
    const editForm = document.getElementById('formEditarTipoPersonal');
    const editIdField = document.getElementById('editar_id');

    if (!tableElement || typeof $ === 'undefined' || !$.fn.DataTable) {
        return;
    }

    const createModalElement = document.getElementById('modalCrearTipoPersonal');
    const editModalElement = document.getElementById('modalEditarTipoPersonal');
    const createModal = createModalElement ? bootstrap.Modal.getOrCreateInstance(createModalElement) : null;
    const editModal = editModalElement ? bootstrap.Modal.getOrCreateInstance(editModalElement) : null;

    const table = $('#tablaTiposPersonal').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: config.dataUrl,
            type: 'GET'
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'tipo', name: 'tipo' },
            { data: 'created_at', name: 'created_at' },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
        ],
        language: {
            url: '/assets/datatables/i18n/es-ES.json'
        }
    });

    function buildUrl(template, id) {
        return template.replace('__ID__', id);
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
        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
            renderValidationErrors(form, prefix, xhr.responseJSON.errors);
            return;
        }

        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: genericMessage
        });
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
                    'X-CSRF-TOKEN': config.csrfToken
                },
                success: function (response) {
                    createModal.hide();
                    createForm.reset();
                    table.ajax.reload(null, false);

                    Swal.fire({
                        icon: 'success',
                        title: 'Correcto',
                        text: response.message
                    });
                },
                error: function (xhr) {
                    handleAjaxError(xhr, createForm, 'error_crear_', 'No se pudo crear el tipo de personal.');
                }
            });
        });
    }

    $('#tablaTiposPersonal').on('click', '.btn-editar', function () {
        const id = this.getAttribute('data-id');

        $.ajax({
            url: buildUrl(config.showUrlTemplate, id),
            type: 'GET',
            success: function (response) {
                const data = response.data || {};

                editIdField.value = data.id || '';
                document.getElementById('editar_tipo').value = data.tipo || '';

                editModal.show();
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar el registro.'
                });
            }
        });
    });

    if (editForm) {
        editForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const id = editIdField.value;
            clearValidation(editForm, 'error_editar_');

            $.ajax({
                url: buildUrl(config.updateUrlTemplate, id),
                type: 'POST',
                data: $(editForm).serialize() + '&_method=PUT',
                headers: {
                    'X-CSRF-TOKEN': config.csrfToken
                },
                success: function (response) {
                    editModal.hide();
                    table.ajax.reload(null, false);

                    Swal.fire({
                        icon: 'success',
                        title: 'Correcto',
                        text: response.message
                    });
                },
                error: function (xhr) {
                    handleAjaxError(xhr, editForm, 'error_editar_', 'No se pudo actualizar el tipo de personal.');
                }
            });
        });
    }

    $('#tablaTiposPersonal').on('click', '.btn-eliminar', function () {
        const id = this.getAttribute('data-id');

        Swal.fire({
            icon: 'warning',
            title: 'Eliminar registro',
            text: 'Esta acción no se puede deshacer.',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                url: buildUrl(config.destroyUrlTemplate, id),
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: config.csrfToken
                },
                headers: {
                    'X-CSRF-TOKEN': config.csrfToken
                },
                success: function (response) {
                    table.ajax.reload(null, false);

                    Swal.fire({
                        icon: 'success',
                        title: 'Correcto',
                        text: response.message
                    });
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo eliminar el registro.'
                    });
                }
            });
        });
    });
});
