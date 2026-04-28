@extends('plantilla.app')

@section('title', 'Sedes')

@section('content')
<div class="app-content-header mx-3 my-3">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h2>Sedes</h2>

        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearSede">
            Nueva Sede
        </button>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        @include('mensajes')

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <table id="tablaSedes" class="table table-bordered w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Creacion</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCrearSede" tabindex="-1" aria-labelledby="modalCrearSedeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formCrearSede">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title" id="modalCrearSedeLabel">Crear Sede</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <label for="crear_nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="crear_nombre" name="nombre" placeholder="Ej: Sede Central">
                    <div class="invalid-feedback" id="error_crear_nombre"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarSede" tabindex="-1" aria-labelledby="modalEditarSedeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formEditarSede">
                @csrf
                <input type="hidden" id="editar_id">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarSedeLabel">Editar Sede</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <label for="editar_nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="editar_nombre" name="nombre">
                    <div class="invalid-feedback" id="error_editar_nombre"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.sedesConfig = {
        dataUrl: '{{ route('sedes.data') }}',
        storeUrl: '{{ route('sedes.store') }}',
        showUrlTemplate: '{{ url('sedes') }}/__ID__',
        updateUrlTemplate: '{{ url('sedes') }}/__ID__',
        destroyUrlTemplate: '{{ url('sedes') }}/__ID__',
        csrfToken: '{{ csrf_token() }}',
    };
</script>
<script src="{{ asset('assets/js/sedes.js') }}"></script>
@endpush
