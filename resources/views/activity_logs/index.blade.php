@extends('plantilla.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Historial de actividad</h2>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form id="filtros-logs">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Log</label>
                        <select name="log_name" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($logNames as $logName)
                                <option value="{{ $logName }}" {{ request('log_name') == $logName ? 'selected' : '' }}>
                                    {{ $logName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Evento</label>
                        <select name="event" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($events as $event)
                                <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>
                                    {{ $event }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Descripción</label>
                        <input type="text" name="description" class="form-control" value="{{ request('description') }}" autocomplete="off">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">ID usuario</label>
                        <input type="text" name="causer_id" class="form-control" value="{{ request('causer_id') }}" autocomplete="off">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Modelo afectado</label>
                        <select name="subject_type" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($subjectTypes as $subjectType)
                                <option value="{{ $subjectType }}" {{ request('subject_type') == $subjectType ? 'selected' : '' }}>
                                    {{ class_basename($subjectType) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Desde</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Hasta</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>

                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        <a href="{{ route('logs.index') }}" class="btn btn-outline-secondary w-100">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table id="tabla-logs" class="table table-bordered table-striped align-middle w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Log</th>
                        <th>Evento</th>
                        <th>Descripción</th>
                        <th>Usuario</th>
                        <th>Subject</th>
                        <th>Fecha</th>
                        <th>Propiedades</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div class="card-footer">
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    const table = $('#tabla-logs').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('logs.data') }}',
            data: function (d) {
                d.log_name = $('select[name="log_name"]').val();
                d.event = $('select[name="event"]').val();
                d.description = $('input[name="description"]').val();
                d.causer_id = $('input[name="causer_id"]').val();
                d.subject_type = $('select[name="subject_type"]').val();
                d.date_from = $('input[name="date_from"]').val();
                d.date_to = $('input[name="date_to"]').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'log_name', name: 'log_name' },
            { data: 'event', name: 'event' },
            { data: 'description', name: 'description' },
            { data: 'usuario', name: 'causer_id', orderable: false, searchable: false },
            { data: 'subject', name: 'subject_type', orderable: false, searchable: false },
            { data: 'fecha', name: 'created_at' },
            { data: 'propiedades', name: 'properties', orderable: false, searchable: false },
        ],
        order: [[0, 'desc']],
        language: {
            url: '{{ asset('assets/datatables/i18n/es-ES.json') }}'
        }
    });

    $('#filtros-logs').on('submit', function (e) {
        e.preventDefault();
        table.ajax.reload();
    });

    $('#filtros-logs .btn-outline-secondary').on('click', function (e) {
        e.preventDefault();
        $('#filtros-logs')[0].reset();
        table.ajax.reload();
    });

    $(document).on('click', '.ver-propiedades', function () {
        const propiedades = $(this).attr('data-propiedades');

        Swal.fire({
            title: 'Propiedades del log',
            html: `<pre style="text-align:left; white-space:pre-wrap;">${propiedades}</pre>`,
            width: 800,
            confirmButtonText: 'Cerrar'
        });
    });
});
</script>
<script>
$(document).on('click', '.ver-propiedades', function () {
    const propiedades = $(this).attr('data-propiedades');

    Swal.fire({
        title: 'Propiedades del log',
        html: `<pre style="text-align:left; white-space:pre-wrap;">${propiedades}</pre>`,
        width: 800,
        confirmButtonText: 'Cerrar'
    });
});
</script>
@endpush

