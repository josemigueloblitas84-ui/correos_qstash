@php
    $selectedInstitucionId = $selectedInstitucionId ?? old('institucion_id');
    $selectedSedeId = $selectedSedeId ?? old('sede_id');
@endphp

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="institucion_id" class="form-label fw-bold">Institucion:</label>
            <select
                class="form-select @error('institucion_id') is-invalid @enderror"
                id="institucion_id"
                name="institucion_id"
                data-sedes-url-template="{{ url('usuarios/institucion') }}/__ID__/sedes">
                <option value="">Seleccione una institucion</option>
                @foreach ($instituciones as $institucion)
                    <option value="{{ $institucion->id }}" {{ (string) $selectedInstitucionId === (string) $institucion->id ? 'selected' : '' }}>
                        {{ $institucion->nombre }}
                    </option>
                @endforeach
            </select>
            @error('institucion_id')
                <div class="invalid-feedback d-block">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="sede_id" class="form-label fw-bold">Sede:</label>
            <select
                class="form-select @error('sede_id') is-invalid @enderror"
                id="sede_id"
                name="sede_id"
                data-selected="{{ $selectedSedeId }}"
                disabled>
                <option value="">Primero seleccione una institucion</option>
            </select>
            @error('sede_id')
                <div class="invalid-feedback d-block">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const institucionSelect = document.getElementById('institucion_id');
            const sedeSelect = document.getElementById('sede_id');

            if (!institucionSelect || !sedeSelect || typeof $ === 'undefined') {
                return;
            }

            const urlTemplate = institucionSelect.dataset.sedesUrlTemplate || '';
            const initialSedeId = sedeSelect.dataset.selected || '';

            function resetSedeSelect(message) {
                sedeSelect.innerHTML = '';

                const option = document.createElement('option');
                option.value = '';
                option.textContent = message;
                sedeSelect.appendChild(option);
                sedeSelect.disabled = true;
            }

            function buildUrl(template, id) {
                return template.replace('__ID__', encodeURIComponent(String(id)));
            }

            function populateSedes(sedes, selectedSedeId) {
                sedeSelect.innerHTML = '';

                if (!Array.isArray(sedes) || sedes.length === 0) {
                    resetSedeSelect('La institucion seleccionada no tiene sedes');
                    return;
                }

                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = 'Seleccione una sede';
                sedeSelect.appendChild(placeholder);

                sedes.forEach(function (sede) {
                    const option = document.createElement('option');
                    option.value = sede.id;
                    option.textContent = sede.nombre || '';

                    if (String(selectedSedeId) === String(sede.id)) {
                        option.selected = true;
                    }

                    sedeSelect.appendChild(option);
                });

                sedeSelect.disabled = false;
            }

            function loadSedes(institucionId, selectedSedeId) {
                if (!institucionId) {
                    resetSedeSelect('Primero seleccione una institucion');
                    return;
                }

                $.ajax({
                    url: buildUrl(urlTemplate, institucionId),
                    type: 'GET',
                    success: function (response) {
                        populateSedes(response.data || [], selectedSedeId);
                    },
                    error: function () {
                        resetSedeSelect('No se pudieron cargar las sedes');
                    }
                });
            }

            institucionSelect.addEventListener('change', function () {
                loadSedes(this.value, '');
            });

            if (institucionSelect.value) {
                loadSedes(institucionSelect.value, initialSedeId);
            } else {
                resetSedeSelect('Primero seleccione una institucion');
            }
        });
    </script>
@endpush
