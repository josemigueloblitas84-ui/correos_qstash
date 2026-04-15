document.addEventListener('DOMContentLoaded', function () {
    const tabla = document.querySelector('#tablaActividadesNoProgramadas tbody');
    const btnAgregar = document.getElementById('btnAgregarActividad');
    const csrfToken = document.querySelector('input[name="_token"]')?.value || '';
    const config = window.informeAgendaConfig || {};
    const agendaIdActual = config.agendaIdActual || null;
    const departamentos = config.departamentos || [];
    const fecha = config.fecha || null;
    const storeNoProgramadaUrl = config.storeNoProgramadaUrl || '';

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function actualizarNumeracion() {
        tabla.querySelectorAll('tr').forEach((fila, index) => {
            const celdaNumero = fila.querySelector('.informe-row-number');
            if (celdaNumero) {
                celdaNumero.textContent = index + 1;
            }

            const agendaId = fila.querySelector('[data-field="agenda_id"]');
            const actividad = fila.querySelector('[data-field="actividad"]');
            const departamentoId = fila.querySelector('[data-field="departamento_id"]');

            if (agendaId) {
                agendaId.name = `no_programadas[${index}][agenda_id]`;
            }

            if (actividad) {
                actividad.name = `no_programadas[${index}][actividad]`;
            }

            if (departamentoId) {
                departamentoId.name = `no_programadas[${index}][departamento_id]`;
            }
        });
    }

    function opcionesDepartamentos() {
        return departamentos.map((departamento) => {
            return `<option value="${departamento.id}">${escapeHtml(departamento.nombre_depa)}</option>`;
        }).join('');
    }

    function renderFilaNoProgramada(item) {
        const fila = document.createElement('tr');

        fila.innerHTML = `
            <td class="informe-row-number"></td>
            <td>
                <span class="informe-actividad-texto">${escapeHtml(item.actividad)}</span>
                <input type="hidden" data-field="agenda_id" value="${escapeHtml(item.agenda_id)}">
                <input type="hidden" data-field="actividad" value="${escapeHtml(item.actividad)}">
                <input type="hidden" data-field="departamento_id" value="${escapeHtml(item.departamento_id)}">
            </td>
            <td>
                <span class="informe-actividad-texto">${escapeHtml(item.equipo)}</span>
            </td>
            <td class="text-center">
                <button type="button" class="informe-btn-remove btnEliminarFila">Quitar</button>
            </td>
        `;

        tabla.appendChild(fila);
        actualizarNumeracion();
    }

    async function guardarNoProgramada(payload) {
        const response = await fetch(storeNoProgramadaUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(data.message || 'No se pudo guardar la actividad no programada.');
        }

        return data;
    }

    btnAgregar.addEventListener('click', function () {
        if (!agendaIdActual || !fecha || !storeNoProgramadaUrl) {
            Swal.fire('Sin datos', 'No existe una agenda activa para registrar actividades no programadas.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Añadir actividad',
            html: `
                <div class="text-start">
                    <label for="swalActividad" class="form-label fw-semibold">Actividad realizada</label>
                    <input
                        type="text"
                        id="swalActividad"
                        class="swal2-input"
                        placeholder="Describa la actividad realizada"
                        style="width: 100%; margin: 0 0 1rem 0;"
                    >

                    <label for="swalDepartamento" class="form-label fw-semibold">Equipo</label>
                    <select
                        id="swalDepartamento"
                        class="swal2-select"
                        style="width: 100%; margin: 0;"
                    >
                        <option value="">Seleccione un equipo</option>
                        ${opcionesDepartamentos()}
                    </select>

                    <div id="swalInlineMessage" class="text-success small mt-3" style="display:none;"></div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cerrar',
            allowOutsideClick: false,
            focusConfirm: false,
            didOpen: () => {
                const popup = Swal.getPopup();
                const confirmButton = Swal.getConfirmButton();
                const actividadInput = popup.querySelector('#swalActividad');
                const departamentoSelect = popup.querySelector('#swalDepartamento');
                const inlineMessage = popup.querySelector('#swalInlineMessage');

                const limpiarCampos = () => {
                    actividadInput.value = '';
                    departamentoSelect.value = '';
                    actividadInput.focus();
                };

                confirmButton.addEventListener('click', async () => {
                    const actividad = actividadInput.value.trim();
                    const departamentoId = departamentoSelect.value;

                    inlineMessage.style.display = 'none';
                    inlineMessage.textContent = '';

                    if (!actividad || !departamentoId) {
                        Swal.showValidationMessage('Debe completar todos los campos');
                        return;
                    }

                    Swal.resetValidationMessage();
                    Swal.showLoading();

                    try {
                        const result = await guardarNoProgramada({
                            agenda_id: agendaIdActual,
                            actividad: actividad,
                            departamento_id: departamentoId,
                            fecha: fecha,
                        });

                        renderFilaNoProgramada(result.item);

                        Swal.hideLoading();
                        inlineMessage.textContent = result.message || 'Actividad registrada correctamente.';
                        inlineMessage.style.display = 'block';

                        limpiarCampos();
                    } catch (error) {
                        Swal.hideLoading();
                        Swal.showValidationMessage(error.message || 'Ocurrió un error al guardar la actividad.');
                    }
                });

                actividadInput.focus();
            },
            preConfirm: () => false
        });
    });

    tabla.addEventListener('click', function (event) {
        if (!event.target.classList.contains('btnEliminarFila')) {
            return;
        }

        event.target.closest('tr').remove();
        actualizarNumeracion();
    });

    actualizarNumeracion();
});
