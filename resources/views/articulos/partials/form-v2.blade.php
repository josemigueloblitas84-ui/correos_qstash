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
                                    <label for="fecha_visual" class="form-label fw-semibold">Fecha:</label>
                                    <input
                                        type="text"
                                        id="fecha_visual"
                                        value="{{ now()->format('d/m/Y') }}"
                                        class="form-control @error('fecha') is-invalid @enderror"
                                        autocomplete="off"
                                        readonly>
                                    <input
                                        type="hidden"
                                        id="fecha"
                                        name="fecha"
                                        value="{{ now()->format('Y-m-d') }}"
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
                                        type="text"
                                        id="fecha_desde"
                                        name="fecha_desde"
                                        value="{{ old('fecha_desde', now()->format('Y-m-d')) }}"
                                        class="form-control @error('fecha_desde') is-invalid @enderror"
                                        autocomplete="off"
                                        readonly
                                        required>
                                    @error('fecha_desde')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="agenda-field">
                                    <label for="fecha_hasta" class="form-label fw-semibold">Al:</label>
                                    <input
                                        type="text"
                                        id="fecha_hasta"
                                        name="fecha_hasta"
                                        value="{{ old('fecha_hasta') }}"
                                        class="form-control @error('fecha_hasta') is-invalid @enderror"
                                        autocomplete="off"
                                        readonly
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

                            <div id="agendaPreviewWrapper" class="agenda-preview-wrapper mt-4">
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
                                                            <input type="text" class="form-control" id="agendaWeekStart" autocomplete="off" readonly>
                                                            <small class="text-muted">Ingrese la fecha de inicio.</small>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="agendaWeekEnd" class="form-label fw-semibold">Fecha Hasta</label>
                                                            <input type="text" class="form-control" id="agendaWeekEnd" autocomplete="off" readonly>
                                                            <small class="text-muted">Solo se permite hasta el último día del mismo mes.</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <label for="agendaActivityName" class="form-label fw-semibold">Actividad</label>
                                            <input type="text" class="form-control" id="agendaActivityName" placeholder="Ingrese la Actividad">
                                        </div>

                                        <div class="col-lg-3">
                                            <label for="agendaEquipo" class="form-label fw-semibold">Departamento</label>
                                            <select class="form-select" id="agendaEquipo">
                                                <option value="">Seleccione</option>
                                                @foreach ($departamentos as $departamento)
                                                    <option value="{{ $departamento->id }}">
                                                        {{ $departamento->nombre_depa }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-3 d-flex align-items-end">
                                            <button type="button" class="btn btn-primary w-100" id="agendaRegisterActivityButton">
                                                Registrar actividad
                                            </button>
                                        </div>
                                    </div>

                                    <div id="agendaActivitiesHistoryWrapper" class="mt-4 d-none">
                                        <div class="row g-4">
                                            <div class="col-12">
                                                <div class="border rounded-3 overflow-hidden">
                                                    <div class="px-3 py-2 border-bottom text-white" style="background-color: #28a745">
                                                        <strong class="text-uppercase small mb-0">Actividades diarias registradas</strong>
                                                    </div>
                                                    <div class="table-responsive">
                                                        <table id="agendaDailyActivitiesTable" class="table align-middle mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>No.</th>
                                                                    <th>Actividad(es)</th>
                                                                    <th>Horas ejecución</th>
                                                                    <th>Equipo</th>
                                                                    <th>Acciones</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody></tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="border rounded-3 overflow-hidden">
                                                    <div class="px-3 py-2 border-bottom text-white" style="background-color: #f0ad4e">
                                                        <strong class="text-uppercase small mb-0">Actividades en la semana registradas</strong>
                                                    </div>
                                                    <div class="table-responsive">
                                                        <table id="agendaWeeklyActivitiesTable" class="table align-middle mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>No.</th>
                                                                    <th>Actividad(es)</th>
                                                                    <th>Fechas ejecución</th>
                                                                    <th>Equipo</th>
                                                                    <th>Acciones</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody></tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-wrap justify-content-end gap-3 mt-4">
                                            <button type="button" class="btn btn-outline-primary px-4" id="agendaPreviewButton">
                                                <i class="fas fa-eye me-2"></i>
                                                Previsualizar agenda
                                            </button>
                                            <button type="button" class="btn btn-success px-4" id="agendaSendButton">
                                                <i class="fas fa-paper-plane me-2"></i>
                                                Enviar agenda
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

<div class="modal fade" id="modalPrevisualizarAgenda" tabindex="-1" aria-labelledby="modalPrevisualizarAgendaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content agenda-preview-modal">
            <div class="modal-header agenda-preview-modal-header">
                <h5 class="modal-title" id="modalPrevisualizarAgendaLabel">Agenda previa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body agenda-preview-modal-body">
                <iframe id="agendaPreviewFrame" class="agenda-preview-frame" src="about:blank" title="Previsualizacion de agenda"></iframe>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarActividadAgenda" tabindex="-1" aria-labelledby="modalEditarActividadAgendaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg agenda-activity-modal">
            <form id="formEditarActividadAgenda">
                @csrf
                <input type="hidden" id="editActivityId">
                <input type="hidden" id="editActivityAgendaId">
                <input type="hidden" id="editActivityType">

                <div class="modal-header border-0 pb-0 agenda-activity-modal-header">
                    <div>
                        <h5 class="modal-title" id="modalEditarActividadAgendaLabel">Editar actividad</h5>
                        <p class="mb-0 agenda-activity-modal-subtitle small" id="editActivityModalSubtitle">Actualiza la actividad seleccionada.</p>
                    </div>
                    <button type="button" class="btn-close agenda-activity-modal-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body pt-4 agenda-activity-modal-body">
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <label for="editActivityName" class="form-label fw-semibold">Actividad</label>
                            <input type="text" class="form-control agenda-activity-input" id="editActivityName" placeholder="Ingrese la Actividad">
                        </div>

                        <div class="col-lg-4">
                            <label for="editActivityDepartment" class="form-label fw-semibold">Departamento</label>
                            <select class="form-select agenda-activity-input" id="editActivityDepartment">
                                <option value="">Seleccione</option>
                                @foreach ($departamentos as $departamento)
                                    <option value="{{ $departamento->id }}">
                                        {{ $departamento->nombre_depa }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12" id="editActivityDailyFields">
                            <div class="agenda-activity-mode-card agenda-activity-mode-card-daily">
                                <div class="agenda-activity-mode-header">
                                    Horario de la actividad diaria
                                </div>
                                <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">De Hrs.</label>
                                    <div class="d-flex gap-2">
                                        <select class="form-select agenda-activity-input" id="editActivityDailyFromHour">
                                            <option value="">0</option>
                                            @for ($i = 0; $i <= 23; $i++)
                                                @php $hora = str_pad($i, 2, '0', STR_PAD_LEFT); @endphp
                                                <option value="{{ $hora }}">{{ $hora }}</option>
                                            @endfor
                                        </select>
                                        <select class="form-select agenda-activity-input" id="editActivityDailyFromMinute">
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
                                        <select class="form-select agenda-activity-input" id="editActivityDailyToHour">
                                            <option value="">0</option>
                                            @for ($i = 0; $i <= 23; $i++)
                                                @php $hora = str_pad($i, 2, '0', STR_PAD_LEFT); @endphp
                                                <option value="{{ $hora }}">{{ $hora }}</option>
                                            @endfor
                                        </select>
                                        <select class="form-select agenda-activity-input" id="editActivityDailyToMinute">
                                            <option value="">00</option>
                                            @foreach (['00', '15', '30', '45'] as $min)
                                                <option value="{{ $min }}">{{ $min }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <small class="text-muted">Hora final.</small>
                                </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-none" id="editActivityWeeklyFields">
                            <div class="agenda-activity-mode-card agenda-activity-mode-card-weekly">
                                <div class="agenda-activity-mode-header">
                                    Fechas de la actividad semanal
                                </div>
                                <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="editActivityWeekStart" class="form-label fw-semibold">Fecha Desde</label>
                                    <input type="text" class="form-control agenda-activity-input" id="editActivityWeekStart" autocomplete="off">
                                    <small class="text-muted">Seleccione la fecha de inicio.</small>
                                </div>

                                <div class="col-md-6">
                                    <label for="editActivityWeekEnd" class="form-label fw-semibold">Fecha Hasta</label>
                                    <input type="text" class="form-control agenda-activity-input" id="editActivityWeekEnd" autocomplete="off">
                                    <small class="text-muted">Seleccione la fecha final permitida.</small>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 agenda-activity-modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary agenda-activity-save-btn">Actualizar actividad</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('articulos.partials.wizard-validation-helper')

@once
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/form-v2.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/jquery-ui/jquery-ui.min.css') }}">
<style>
    .ui-autocomplete {
        z-index: 2000;
        max-height: 240px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .ui-datepicker {
        z-index: 2055 !important;
    }

    .agenda-activity-modal {
        border-radius: 18px;
        overflow: hidden;
    }

    .agenda-activity-modal-header {
        padding: 1.25rem 1.5rem 0.5rem;
        background: linear-gradient(135deg, #1f5f9a 0%, #2e79b8 100%);
        color: #fff;
    }

    .agenda-activity-modal-header .modal-title {
        font-size: 1.25rem;
        font-weight: 700;
        letter-spacing: 0.01em;
    }

    .agenda-activity-modal-subtitle {
        color: rgba(255, 255, 255, 0.82);
    }

    .agenda-activity-modal-close {
        filter: invert(1);
        opacity: 0.9;
    }

    .agenda-activity-modal-body {
        padding: 1.5rem;
        background: linear-gradient(180deg, #f7fbff 0%, #ffffff 100%);
    }

    .agenda-activity-input {
        min-height: 46px;
        border-radius: 12px;
        border-color: #d8e2ef;
        box-shadow: none;
    }

    .agenda-activity-input:focus {
        border-color: #2e79b8;
        box-shadow: 0 0 0 0.2rem rgba(46, 121, 184, 0.12);
    }

    .agenda-activity-mode-card {
        border: 1px solid #e4ecf5;
        border-radius: 16px;
        padding: 1rem;
        background: #fff;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }

    .agenda-activity-mode-card-daily {
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
    }

    .agenda-activity-mode-card-weekly {
        background: linear-gradient(180deg, #fffaf2 0%, #ffffff 100%);
    }

    .agenda-activity-mode-header {
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #47627f;
        margin-bottom: 0.9rem;
    }

    .agenda-activity-modal-footer {
        padding: 0 1.5rem 1.4rem;
        background: #fff;
    }

    .agenda-activity-save-btn {
        min-width: 180px;
        border-radius: 12px;
        font-weight: 600;
    }

    .agenda-preview-modal {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
    }

    .agenda-preview-modal-header {
        background: linear-gradient(180deg, #eef9df 0%, #dff1c8 100%);
        border-bottom: 1px solid #d7e8bf;
        padding: 1rem 1.5rem;
    }

    .agenda-preview-modal-header .modal-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #5b7f3a;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .agenda-preview-modal-body {
        background: #eff4ea;
        padding: 1.25rem;
    }

    .agenda-preview-frame {
        width: 100%;
        min-height: 78vh;
        border: 0;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: inset 0 0 0 1px rgba(104, 133, 76, 0.16);
    }

    @media (max-width: 991.98px) {
        .agenda-preview-frame {
            min-height: 68vh;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    window.formV2Config = {
        todayString: '{{ now()->format('Y-m-d') }}',
        agendaDataUrl: '{{ route('articulos.agenda.data') }}',
        agendaShowUrl: '{{ url('formMultiPasos/agenda-registrada') }}/__ID__',
        agendaPreviewUrl: '{{ url('formMultiPasos/agenda-registrada') }}/__ID__/preview',
        agendaSendUrl: '{{ url('formMultiPasos/agenda-registrada') }}/__ID__/send',
        agendaDestroyUrl: '{{ url('formMultiPasos/agenda-registrada') }}/__ID__',
        activityDataUrl: '{{ route('articulos.actividades.data') }}',
        activityStoreUrl: '{{ route('articulos.actividades.store') }}',
        activityShowUrl: '{{ url('formMultiPasos/agenda-actividades') }}/__ID__',
        activityUpdateUrl: '{{ url('formMultiPasos/agenda-actividades') }}/__ID__',
        activityDestroyUrl: '{{ url('formMultiPasos/agenda-actividades') }}/__ID__',
        activityAutocompleteUrl: '{{ route('articulos.actividades.autocomplete') }}',
        usuariosPorDepartamentoUrl: '{{ url('formMultiPasos/usuarios-por-departamento') }}/__ID__',
    };
</script>
<script src="{{ asset('assets/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('assets/js/form-v2.js') }}"></script>
@endpush
@endonce
