@extends('plantilla.app')

@section('title', 'Reporte Agenda Informe')

@section('content')
    <div class="app-content-header py-3">
        <div class="container-fluid">
            <h2 class="mb-0">Reporte Agenda Informe</h2>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card reporte-card shadow-sm">
                <div class="reporte-card__header">
                    AGENDAS REGISTRADAS
                </div>

                <div class="card-body">
                    <form id="reporteAgendaInformeForm">
                        <div class="row g-4 align-items-start">
                            <div class="col-lg-3 col-md-6">
                                <label for="fecha_desde" class="form-label reporte-label">Fecha Desde:</label>
                                <input
                                    type="text"
                                    id="fecha_desde"
                                    name="fecha_desde"
                                    class="form-control reporte-input"
                                    value="{{ now()->format('Y-m-d') }}"
                                    autocomplete="off"
                                    readonly
                                >
                                <small class="text-muted">Ingrese la fecha de inicio.</small>

                                <div class="mt-3">
                                    <label for="equipo" class="form-label reporte-label">Equipo:</label>
                                    <select id="equipo" name="equipo" class="form-select reporte-input">
                                        <option value="">Seleccione</option>
                                        @foreach ($departamentos as $departamento)
                                            <option value="{{ $departamento->id }}">
                                                {{ $departamento->nombre_depa }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Seleccione un equipo.</small>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <label for="fecha_hasta" class="form-label reporte-label">Fecha Hasta:</label>
                                <input
                                    type="text"
                                    id="fecha_hasta"
                                    name="fecha_hasta"
                                    class="form-control reporte-input"
                                    value="{{ now()->format('Y-m-d') }}"
                                    autocomplete="off"
                                    readonly
                                >
                                <small class="text-muted">Ingrese la fecha final.</small>
                            </div>

                            <div class="col-lg-4 col-md-8">
                                <label class="form-label reporte-label d-block">Seleccione las opciones a buscar:</label>

                                <div class="d-flex flex-wrap gap-4 pt-2">
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="radio"
                                            name="tipo_busqueda"
                                            id="tipo_agenda"
                                            value="agenda"
                                            checked
                                        >
                                        <label class="form-check-label reporte-radio-label" for="tipo_agenda">
                                            AGENDA
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="radio"
                                            name="tipo_busqueda"
                                            id="tipo_informe"
                                            value="informe"
                                        >
                                        <label class="form-check-label reporte-radio-label" for="tipo_informe">
                                            INFORME DE AGENDA
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-2 col-md-4 d-flex align-items-start justify-content-lg-end">
                                <button type="submit" class="btn reporte-btn-buscar w-100">
                                    Buscar
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-4" id="agendaTableWrapper">
                        <div class="card shadow-sm reporte-subcard">
                            <div class="card-body table-responsive">
                                <table id="tablaReporteAgenda" class="table table-bordered table-striped align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th>Fecha de elaboración</th>
                                            <th>Nombre y apellido</th>
                                            <th>Equipo</th>
                                            <th>Fechas agendadas</th>
                                            <th>Opciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-none" id="informeAgendaWrapper">
                        <div class="card shadow-sm reporte-subcard">
                            <div class="card-body table-responsive">
                                <table id="tablaReporteInformeAgenda" class="table table-bordered table-striped align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th>Fecha de elaboración del informe</th>
                                            <th>Nombre y apellido</th>
                                            <th>Equipo</th>
                                            <th>Visualizar</th>
                                            @if ($canValidateInforme)
                                                <th>Validar</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPrevisualizarAgendaReporte" tabindex="-1" aria-labelledby="modalPrevisualizarAgendaReporteLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content agenda-preview-modal">
                <div class="modal-header agenda-preview-modal-header">
                    <h5 class="modal-title" id="modalPrevisualizarAgendaReporteLabel">VISTA PREVIA</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body agenda-preview-modal-body">
                    <iframe
                        id="agendaPreviewFrameReporte"
                        class="agenda-preview-frame"
                        src="about:blank"
                        title="Previsualizacion PDF"
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ global_asset('assets/css/reporte_agenda_informe.css') }}">
    <link rel="stylesheet" href="{{ global_asset('assets/plugins/jquery-ui/jquery-ui.min.css') }}">
@endpush

@push('scripts')
    <script>
        window.reporteAgendaInformeConfig = {
            dataUrl: '{{ route('reporte-agenda-informe.data') }}',
            dataInformeUrl: '{{ route('reporte-agenda-informe.data-informe') }}',
            agendaPreviewUrlTemplate: '{{ url('reporte-agenda-informe/agenda') }}/__ID__/preview',
            informePreviewUrl: '{{ route('reporte-agenda-informe.informe.preview') }}',
            validarInformeUrl: '{{ route('reporte-agenda-informe.informe.validar') }}',
            csrfToken: '{{ csrf_token() }}',
            canValidateInforme: @json($canValidateInforme)
        };
    </script>
    <script src="{{ global_asset('assets/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{ global_asset('assets/js/reporte_agenda_informe.js') }}"></script>
@endpush
