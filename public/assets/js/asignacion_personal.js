document.addEventListener('DOMContentLoaded', function () {
    const select = $('#usuarios_asignados_select');
    const container = document.getElementById('usuariosSeleccionadosContainer');
    const form = document.getElementById('formAsignarPersonal');
    const submitButton = document.getElementById('btnAsignarPersonal');
    const config = window.personalAsignadoConfig || {};

    if (!select.length || !container || !form) {
        return;
    }

    select.select2({
        placeholder: 'Buscar y seleccionar personal',
        width: '100%',
        language: {
            noResults: function () {
                return 'No se encontraron usuarios';
            }
        }
    });

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    function renderSeleccionados() {
        const selectedOptions = Array.from(select[0].selectedOptions);

        if (selectedOptions.length === 0) {
            container.innerHTML = `
                <div class="col-12">
                    <div class="personal-empty">
                        No hay personal seleccionado.
                    </div>
                </div>
            `;
            return;
        }

        container.innerHTML = selectedOptions.map(option => `
            <div class="col-md-4">
                <div class="personal-card">
                    <button
                        type="button"
                        class="personal-card__remove"
                        data-remove-id="${option.value}"
                        title="Quitar"
                        aria-label="Quitar"
                    >
                        &times;
                    </button>

                    <div class="personal-card__name">
                        ${escapeHtml(option.text)}
                    </div>

                    <div class="personal-card__item">
                        <strong>Correo:</strong> ${escapeHtml(option.dataset.email)}
                    </div>

                    <div class="personal-card__item">
                        <strong>Departamento:</strong> ${escapeHtml(option.dataset.departamento)}
                    </div>

                    <div class="personal-card__item">
                        <strong>Tipo:</strong> ${escapeHtml(option.dataset.tipo)}
                    </div>
                </div>
            </div>
        `).join('');
    }

    select.on('change', function () {
        renderSeleccionados();
    });

    container.addEventListener('click', function (event) {
        const button = event.target.closest('[data-remove-id]');
        if (!button) {
            return;
        }

        const optionId = button.getAttribute('data-remove-id');
        const option = select[0].querySelector(`option[value="${optionId}"]`);

        if (option) {
            option.selected = false;
            select.trigger('change');
        }
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        Swal.fire({
            title: '¿Está seguro?',
            text: 'Se guardará la asignación del personal seleccionado.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, asignar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Asignando...';
            }

            form.submit();
        });
    });

    renderSeleccionados();

    if (config.successMessage) {
        Swal.fire({
            icon: 'success',
            title: 'Correcto',
            text: config.successMessage,
            confirmButtonText: 'OK'
        });
    }
});
