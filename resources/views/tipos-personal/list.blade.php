@extends('plantilla.app')

@section('title', 'Tipos de Personal')

@section('content')
<div class="app-content-header mx-3 my-3">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h2>Tipos de Personal</h2>

        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearTipoPersonal">
            Nuevo Tipo
        </button>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <table id="tablaTiposPersonal" class="table table-bordered w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCrearTipoPersonal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formCrearTipoPersonal">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Crear Tipo de Personal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label class="form-label">Tipo</label>
                    <input type="text" class="form-control" id="crear_tipo" name="tipo" placeholder="Ej: Becario">
                    <div class="invalid-feedback" id="error_crear_tipo"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarTipoPersonal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formEditarTipoPersonal">
                @csrf
                <input type="hidden" id="editar_id">

                <div class="modal-header">
                    <h5 class="modal-title">Editar Tipo de Personal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label class="form-label">Tipo</label>
                    <input type="text" class="form-control" id="editar_tipo" name="tipo">
                    <div class="invalid-feedback" id="error_editar_tipo"></div>
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
    window.tiposPersonalConfig = {
        dataUrl: '{{ route('tipos-personal.data') }}',
        storeUrl: '{{ route('tipos-personal.store') }}',
        showUrlTemplate: '{{ url('tipos-personal') }}/__ID__',
        updateUrlTemplate: '{{ url('tipos-personal') }}/__ID__',
        destroyUrlTemplate: '{{ url('tipos-personal') }}/__ID__',
        csrfToken: '{{ csrf_token() }}',
    };
</script>
<script src="{{ global_asset('assets/js/tipos-personal.js') }}"></script>
@endpush
