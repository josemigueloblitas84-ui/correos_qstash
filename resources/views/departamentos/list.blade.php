@extends('plantilla.app')

@section('title', 'Departamentos')

@section('content')
<div class="app-content-header mx-3 my-3">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h2>Departamentos</h2>

        <button type="button" class="btn departamento-primary-btn" data-bs-toggle="modal" data-bs-target="#modalCrearDepartamento">
            <i class="bi bi-plus-lg me-1"></i>
            Nuevo Departamento
        </button>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        @include('mensajes')

        <div class="card departamento-card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="departamento-table-shell">
                    <table id="tablaDepartamentos" class="table departamento-table align-middle w-100 mb-0">
                        <thead>
                            <tr>
                                <th width="80">#</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Creación</th>
                                <th width="220">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade departamento-modal" id="modalCrearDepartamento" tabindex="-1" aria-labelledby="modalCrearDepartamentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <form id="formCrearDepartamento">
                @csrf

                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title" id="modalCrearDepartamentoLabel">Crear Departamento</h5>
                        <p class="departamento-modal-subtitle mb-0">Registra un nuevo departamento o unidad para usarlo luego en agenda.</p>
                    </div>
                    <button type="button" class="btn-close departamento-close-btn" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body pt-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="crear_nombre_depa" class="form-label">Nombre</label>
                            <input type="text" class="form-control departamento-input" id="crear_nombre_depa" name="nombre_depa" placeholder="Ej: Sistemas">
                            <div class="invalid-feedback" id="error_crear_nombre_depa"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="crear_estado_depa" class="form-label">Estado</label>
                            <select class="form-select departamento-input" id="crear_estado_depa" name="estado_depa">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                            <div class="invalid-feedback" id="error_crear_estado_depa"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn departamento-secondary-btn" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn departamento-primary-btn">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade departamento-modal" id="modalEditarDepartamento" tabindex="-1" aria-labelledby="modalEditarDepartamentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <form id="formEditarDepartamento">
                @csrf
                <input type="hidden" id="editar_id">

                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title" id="modalEditarDepartamentoLabel">Editar Departamento</h5>
                        <p class="departamento-modal-subtitle mb-0">Actualiza los datos del departamento seleccionado.</p>
                    </div>
                    <button type="button" class="btn-close departamento-close-btn" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body pt-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="editar_nombre_depa" class="form-label">Nombre</label>
                            <input type="text" class="form-control departamento-input" id="editar_nombre_depa" name="nombre_depa">
                            <div class="invalid-feedback" id="error_editar_nombre_depa"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="editar_estado_depa" class="form-label">Estado</label>
                            <select class="form-select departamento-input" id="editar_estado_depa" name="estado_depa">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                            <div class="invalid-feedback" id="error_editar_estado_depa"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn departamento-secondary-btn" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn departamento-primary-btn">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ global_asset('assets/css/departamentos.css') }}">
@endpush

@push('scripts')
<script>
    window.departamentosConfig = {
        dataUrl: '{{ route('departamentos.data') }}',
        storeUrl: '{{ route('departamentos.store') }}',
        showUrlTemplate: '{{ url('departamentos') }}/__ID__',
        updateUrlTemplate: '{{ url('departamentos') }}/__ID__',
        toggleStatusUrlTemplate: '{{ url('departamentos') }}/__ID__/toggle-status',
        destroyUrlTemplate: '{{ url('departamentos') }}/__ID__',
        csrfToken: '{{ csrf_token() }}',
    };
</script>
<script src="{{ global_asset('assets/js/departamentos.js') }}"></script>
@endpush
