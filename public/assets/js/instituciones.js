document.addEventListener('DOMContentLoaded', function () {
    const config = window.institucionesConfig || {};
    const tableElement = document.getElementById('tablaInstituciones');
    const createForm = document.getElementById('formCrearInstitucion');
    const editForm = document.getElementById('formEditarInstitucion');
    const editIdField = document.getElementById('editar_id');
    const adminForm = document.getElementById('formCrearAdministrador');
    const adminTenantIdField = document.getElementById('administrador_tenant_id');
    const adminInstitutionField = document.getElementById('administrador_institucion');

    if (!tableElement || typeof $ === 'undefined' || !$.fn.DataTable) {
        return;
    }

    const createModalElement = document.getElementById('modalCrearInstitucion');
    const editModalElement = document.getElementById('modalEditarInstitucion');
    const adminModalElement = document.getElementById('modalCrearAdministrador');
    const createModal = createModalElement ? bootstrap.Modal.getOrCreateInstance(createModalElement) : null;
    const editModal = editModalElement ? bootstrap.Modal.getOrCreateInstance(editModalElement) : null;
    const adminModal = adminModalElement ? bootstrap.Modal.getOrCreateInstance(adminModalElement) : null;

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
            { data: 'dominios', name: 'dominios', orderable: false, searchable: false },
            { data: 'base_datos', name: 'base_datos', orderable: false, searchable: false },
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

    function resetAdminForm() {
        if (!adminForm) {
            return;
        }

        adminForm.reset();
        clearValidation(adminForm, 'error_admin_');

        if (adminTenantIdField) {
            adminTenantIdField.value = '';
        }

        if (adminInstitutionField) {
            adminInstitutionField.value = '';
        }
    }

    if (createModalElement) {
        createModalElement.addEventListener('hidden.bs.modal', resetCreateForm);
    }

    if (editModalElement) {
        editModalElement.addEventListener('hidden.bs.modal', resetEditForm);
    }

    if (adminModalElement) {
        adminModalElement.addEventListener('hidden.bs.modal', resetAdminForm);
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
                        text: 'Creando institucion, dominio y base tenant',
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
                const tenantIdField = document.getElementById('editar_tenant_id');
                const nombreField = document.getElementById('editar_nombre');
                const domainField = document.getElementById('editar_domain');

                if (editIdField) {
                    editIdField.value = data.id || '';
                }

                if (tenantIdField) {
                    tenantIdField.value = data.id || '';
                }

                if (nombreField) {
                    nombreField.value = data.nombre || '';
                }

                if (domainField) {
                    domainField.value = data.domain || '';
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

    $('#tablaInstituciones').on('click', '.btn-conectar', function () {
        const id = this.getAttribute('data-id');
        const nombre = this.getAttribute('data-nombre') || id;

        if (!id || !config.connectUrlTemplate) {
            return;
        }

        const tenantWindow = window.open('about:blank', '_blank');

        if (tenantWindow) {
            tenantWindow.opener = null;
            tenantWindow.document.title = 'Conectando...';
            tenantWindow.document.body.innerHTML = '<p style="font-family: sans-serif; padding: 24px;">Conectando con la institucion...</p>';
        }

        $.ajax({
            url: buildUrl(config.connectUrlTemplate, id),
            type: 'POST',
            data: {
                _token: config.csrfToken,
            },
            headers: {
                'X-CSRF-TOKEN': config.csrfToken,
            },
            beforeSend: function () {
                Swal.fire({
                    title: 'Conectando...',
                    text: 'Cargando modulos y registros de ' + nombre,
                    allowOutsideClick: false,
                    didOpen: function () {
                        Swal.showLoading();
                    },
                });
            },
            success: function (response) {
                Swal.close();

                if (response.redirect_url) {
                    if (tenantWindow) {
                        tenantWindow.location.href = response.redirect_url;
                    } else {
                        window.location.href = response.redirect_url;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Conexion abierta',
                        text: 'El tenant se abrio en una nueva pestana.',
                        timer: 1500,
                        showConfirmButton: false,
                    });
                    return;
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Conectado',
                    text: response.message || 'Conexion establecida con la institucion.',
                    timer: 1500,
                    showConfirmButton: false,
                });
            },
            error: function (xhr) {
                if (tenantWindow) {
                    tenantWindow.close();
                }

                handleAjaxError(xhr, editForm || createForm, 'error_editar_', 'No se pudo conectar con la institucion.');
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

    $('#tablaInstituciones').on('click', '.btn-administrador', function () {
        const id = this.getAttribute('data-id');
        const nombre = this.getAttribute('data-nombre') || id;

        if (!id || !adminForm) {
            return;
        }

        resetAdminForm();

        if (adminTenantIdField) {
            adminTenantIdField.value = id;
        }

        if (adminInstitutionField) {
            adminInstitutionField.value = nombre;
        }

        if (adminModal) {
            adminModal.show();
        }
    });

    if (adminForm) {
        adminForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const id = adminTenantIdField ? adminTenantIdField.value : '';
            if (!id) {
                return;
            }

            clearValidation(adminForm, 'error_admin_');

            $.ajax({
                url: buildUrl(config.adminStoreUrlTemplate, id),
                type: 'POST',
                data: $(adminForm).serialize(),
                headers: {
                    'X-CSRF-TOKEN': config.csrfToken,
                },
                beforeSend: function () {
                    Swal.fire({
                        title: 'Creando administrador...',
                        text: 'Preparando roles, permisos y usuario dentro de la institucion',
                        allowOutsideClick: false,
                        didOpen: function () {
                            Swal.showLoading();
                        },
                    });
                },
                success: function (response) {
                    Swal.close();

                    if (adminModal) {
                        adminModal.hide();
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Correcto',
                        text: response.message || 'Administrador creado correctamente.',
                    });
                },
                error: function (xhr) {
                    handleAjaxError(xhr, adminForm, 'error_admin_', 'No se pudo crear el administrador.');
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
            title: 'Dar de baja institucion',
            text: 'La institucion dejara de aparecer y no podra ingresar por su dominio, pero su base de datos tenant se conservara.',
            showCancelButton: true,
            confirmButtonText: 'Si, dar de baja',
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
                        title: 'Procesando...',
                        text: 'Dando de baja la institucion',
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
                        text: response.message || 'Institucion dada de baja correctamente.',
                    });
                },
                error: function (xhr) {
                    handleAjaxError(xhr, editForm || createForm, 'error_editar_', 'No se pudo dar de baja la institucion.');
                },
            });
        });
    });
});
