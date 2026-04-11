<form action="{{ $action }}" method="POST" id="articuloWizardFormV2" novalidate>
    @csrf
    @if ($method !== 'POST')
    @method($method)
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="wizard-v2-shell">
                <div class="wizard-v2-body">
                    <div class="agenda-step-shell">
                        <div class="agenda-step-titlebar">
                            ENCABEZADO DE LA AGENDA
                        </div>

                        <div class="agenda-step-body">
                            <div class="agenda-grid">
                                <div class="agenda-field">
                                    <label for="fecha" class="form-label fw-semibold">Fecha:</label>
                                    <input
                                        type="date"
                                        id="fecha"
                                        name="fecha"
                                        value="{{ old('fecha', data_get($articulo ?? null, 'fecha', now()->format('Y-m-d'))) }}"
                                        class="form-control @error('fecha') is-invalid @enderror"
                                        readonly
                                        required>
                                    @error('fecha')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="agenda-field">
                                    <label for="cod_unidad" class="form-label fw-semibold">Departamento o Unidad:</label>
                                    <select
                                        id="cod_unidad"
                                        name="cod_unidad"
                                        class="form-select @error('cod_unidad') is-invalid @enderror"
                                        required>
                                        <option value="">Seleccione</option>
                                        @foreach ($departamentos as $departamento)
                                            <option value="{{ $departamento->id }}" @selected(old('cod_unidad') == $departamento->id)>
                                                {{ $departamento->nombre_depa }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('cod_unidad')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="agenda-field agenda-field-wide">
                                    <label for="cod_solicitante" class="form-label fw-semibold">Nombre y Apellido:</label>
                                    <select name="cod_solicitante" id="cod_solicitante"
                                        class="form-select @error('cod_solicitante') is-invalid @enderror"
                                        required>
                                        <option value="">Seleccione un departartamento primero</option>
                                    </select>
                                    @error('cod_solicitante')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="agenda-field">
                                    <label for="cargo_visual" class="form-label fw-semibold">Cargo:</label>
                                    <input
                                        type="text"
                                        id="cargo_visual"
                                        class="form-control"
                                        value=""
                                        placeholder="Se completara automaticamente"
                                        readonly>
                                </div>

                                <div class="agenda-field">
                                    <label for="fecha_desde" class="form-label fw-semibold">Periodo Del:</label>
                                    <input
                                        type="date"
                                        id="fecha_desde"
                                        name="fecha_desde"
                                        value="{{ old('fecha_desde', now()->format('Y-m-d')) }}"
                                        min="{{ now()->format('Y-m-d') }}"
                                        class="form-control @error('fecha_desde') is-invalid @enderror"
                                        required>
                                    @error('fecha_desde')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="agenda-field">
                                    <label for="fecha_hasta" class="form-label fw-semibold">Al:</label>
                                    <input
                                        type="date"
                                        id="fecha_hasta"
                                        name="fecha_hasta"
                                        value="{{ old('fecha_hasta') }}"
                                        min="{{ old('fecha_desde', now()->format('Y-m-d')) }}"
                                        class="form-control @error('fecha_hasta') is-invalid @enderror"
                                        required>
                                    @error('fecha_hasta')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="agenda-field">
                                        <label class="form-label fw-semibold">Horario de trabajo De Hrs.:</label>
                                        <div class="agenda-time-group">
                                            <div class="agenda-time-select">
                                                <select
                                                    name="hora_inicio_hora"
                                                    class="form-select @error('hora_inicio_hora') is-invalid @enderror"
                                                    required>
                                                    <option value="">0</option>
                                                    @for ($i = 0; $i <= 23; $i++)
                                                        @php $hora = str_pad($i, 2, '0', STR_PAD_LEFT); @endphp
                                                        <option value="{{ $hora }}" @selected(old('hora_inicio_hora') === $hora)>{{ $hora }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                            <span class="agenda-time-separator">:</span>
                                            <div class="agenda-time-select">
                                                <select
                                                    name="hora_inicio_minuto"
                                                    class="form-select @error('hora_inicio_minuto') is-invalid @enderror"
                                                    required>
                                                    <option value="">00</option>
                                                    @foreach (['00', '15', '30', '45'] as $min)
                                                        <option value="{{ $min }}" @selected(old('hora_inicio_minuto') === $min)>{{ $min }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <small class="text-muted">Hora Inicio.</small>
                                        @error('hora_inicio_hora')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        @error('hora_inicio_minuto')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                <div class="agenda-field">
                                    <label class="form-label fw-semibold">A Hrs. :</label>
                                    <div class="agenda-time-group">
                                        <div class="agenda-time-select">
                                            <select
                                                name="hora_fin_hora"
                                                class="form-select @error('hora_fin_hora') is-invalid @enderror"
                                                required>
                                                <option value="">0</option>
                                                @for ($i = 0; $i <= 23; $i++)
                                                    @php $hora = str_pad($i, 2, '0', STR_PAD_LEFT); @endphp
                                                    <option value="{{ $hora }}" @selected(old('hora_fin_hora') === $hora)>{{ $hora }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <span class="agenda-time-separator">:</span>
                                        <div class="agenda-time-select">
                                            <select
                                                name="hora_fin_minuto"
                                                class="form-select @error('hora_fin_minuto') is-invalid @enderror"
                                                required>
                                                <option value="">00</option>
                                                @foreach (['00', '15', '30', '45'] as $min)
                                                    <option value="{{ $min }}" @selected(old('hora_fin_minuto') === $min)>{{ $min }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <small class="text-muted">Hora final.</small>
                                    @error('hora_fin_hora')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('hora_fin_minuto')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="agenda-field agenda-field-button">
                                    <button type="button" class="btn agenda-inline-btn" data-action="validate-agenda">
                                        Programar agenda
                                    </button>
                                </div>
                            </div>

                            <div id="agendaPreviewWrapper" class="agenda-preview-wrapper d-none mt-4">
                                <div class="card border-0 shadow-sm">
                                    <div class="agenda-preview-header">
                                        AGENDA REGISTRADA
                                    </div>

                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table id="agendaRegistradaTable" class="table align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>No.</th>
                                                        <th>Fecha de Registro</th>
                                                        <th>Departamento</th>
                                                        <th>Nombre y Apellido</th>
                                                        <th>Periodo</th>
                                                        <th>Horario de Trabajo</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="agendaWeeklyWrapper" class="card border-0 shadow-sm d-none mt-4">
                                <div class="agenda-preview-header text-uppercase">
                                    Programar agenda semanal
                                </div>

                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-lg-6">
                                            <div class="border rounded-3 h-100" id="agendaDailyCard">
                                                <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center text-white" style="background-color: #2e79b8">
                                                    <strong class="text-uppercase small mb-0">Actividades diarias</strong>
                                                    <div class="form-check m-0">
                                                        <input class="form-check-input" type="radio" name="agenda_activity_mode" id="agendaActivityDaily" value="daily" checked>
                                                    </div>
                                                </div>
                                                <div class="p-3" id="agendaDailyBody">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold">De Hrs.</label>
                                                            <div class="d-flex gap-2">
                                                                <select class="form-select" id="agendaDailyFromHour">
                                                                    <option value="">0</option>
                                                                    @for ($i = 0; $i <= 23; $i++)
                                                                        @php $hora = str_pad($i, 2, '0', STR_PAD_LEFT); @endphp
                                                                        <option value="{{ $hora }}">{{ $hora }}</option>
                                                                    @endfor
                                                                </select>
                                                                <select class="form-select" id="agendaDailyFromMinute">
                                                                    <option value="">00</option>
                                                                    @foreach (['00', '15', '30', '45'] as $min)
                                                                        <option value="{{ $min }}">{{ $min }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <small class="text-muted">Hora inicial.</small>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold">A Hrs.</label>
                                                            <div class="d-flex gap-2">
                                                                <select class="form-select" id="agendaDailyToHour">
                                                                    <option value="">0</option>
                                                                    @for ($i = 0; $i <= 23; $i++)
                                                                        @php $hora = str_pad($i, 2, '0', STR_PAD_LEFT); @endphp
                                                                        <option value="{{ $hora }}">{{ $hora }}</option>
                                                                    @endfor
                                                                </select>
                                                                <select class="form-select" id="agendaDailyToMinute">
                                                                    <option value="">00</option>
                                                                    @foreach (['00', '15', '30', '45'] as $min)
                                                                        <option value="{{ $min }}">{{ $min }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <small class="text-muted">Hora final.</small>
                                                        </div>
                                                    </div>
                                                    <p class="text-muted small mb-0 mt-3 text-center">
                                                        Horas de ejecucion. Formato de 24 horas.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="border rounded-3 h-100" id="agendaWeeklyCard">
                                                <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center text-white" style="background-color: #2e79b8">
                                                    <strong class="text-uppercase small mb-0">Actividades en la semana</strong>
                                                    <div class="form-check m-0">
                                                        <input class="form-check-input" type="radio" name="agenda_activity_mode" id="agendaActivityWeekly" value="weekly">
                                                    </div>
                                                </div>
                                                <div class="p-3" id="agendaWeeklyBody">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label for="agendaWeekStart" class="form-label fw-semibold">Fecha Desde</label>
                                                            <input type="date" class="form-control" id="agendaWeekStart">
                                                            <small class="text-muted">Ingrese la fecha de inicio.</small>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="agendaWeekEnd" class="form-label fw-semibold">Fecha Hasta</label>
                                                            <input type="date" class="form-control" id="agendaWeekEnd">
                                                            <small class="text-muted">Ingrese la fecha final.</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <label for="agendaActivityName" class="form-label fw-semibold">Actividad</label>
                                            <input type="text" class="form-control" id="agendaActivityName" placeholder="Filtrar">
                                        </div>

                                        <div class="col-lg-3">
                                            <label for="agendaEquipo" class="form-label fw-semibold">Equipo</label>
                                            <select class="form-select" id="agendaEquipo">
                                                <option value="">Seleccione</option>
                                                <option value="Equipo 1">Equipo 1</option>
                                                <option value="Equipo 2">Equipo 2</option>
                                                <option value="Equipo 3">Equipo 3</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-3 d-flex align-items-end">
                                            <button type="button" class="btn btn-primary w-100" id="agendaRegisterActivityButton">
                                                Registrar actividad
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@include('articulos.partials.wizard-validation-helper')

@once
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/form-v2.css') }}">
@endpush

@push('scripts')
<script>
    window.formV2Config = {
        todayString: '{{ now()->format('Y-m-d') }}',
        agendaDataUrl: '{{ route('articulos.agenda.data') }}',
        usuariosPorDepartamentoUrl: '{{ url('formMultiPasos/usuarios-por-departamento') }}/__ID__',
    };
</script>
<script src="{{ asset('assets/js/form-v2.js') }}"></script>
@endpush
@endonce
