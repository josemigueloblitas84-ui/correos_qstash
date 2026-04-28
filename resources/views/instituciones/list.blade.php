@extends('plantilla.app')

@section('title', 'Instituciones')

@section('content')
<div class="app-content-header mx-3 my-3">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h2>Instituciones</h2>

        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearInstitucion">
            Nueva Institucion
        </button>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        @include('mensajes')

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <table id="tablaInstituciones" class="table table-bordered w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Sedes</th>
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

<div class="modal fade" id="modalCrearInstitucion" tabindex="-1" aria-labelledby="modalCrearInstitucionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formCrearInstitucion">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title" id="modalCrearInstitucionLabel">Crear Institucion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <label for="crear_nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="crear_nombre" name="nombre" placeholder="Ej: Unifranz">
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

<div class="modal fade" id="modalEditarInstitucion" tabindex="-1" aria-labelledby="modalEditarInstitucionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formEditarInstitucion">
                @csrf
                <input type="hidden" id="editar_id">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarInstitucionLabel">Editar Institucion</h5>
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

<div class="modal fade" id="modalSedesInstitucion" tabindex="-1" aria-labelledby="modalSedesInstitucionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formSedesInstitucion">
                @csrf
                <input type="hidden" id="sedes_institucion_id">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalSedesInstitucionLabel">Asignar Sedes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <p class="mb-3">
                        Institucion:
                        <span class="fw-semibold" id="sedes_institucion_nombre">-</span>
                    </p>

                    <div class="d-flex flex-column gap-2" id="listaSedesInstitucion"></div>
                    <div class="invalid-feedback d-block" id="error_asignar_sedes"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.institucionesConfig = {
        dataUrl: '{{ route('instituciones.data') }}',
        storeUrl: '{{ route('instituciones.store') }}',
        showUrlTemplate: '{{ url('instituciones') }}/__ID__',
        updateUrlTemplate: '{{ url('instituciones') }}/__ID__',
        destroyUrlTemplate: '{{ url('instituciones') }}/__ID__',
        sedesEditUrlTemplate: '{{ url('instituciones') }}/__ID__/sedes',
        sedesUpdateUrlTemplate: '{{ url('instituciones') }}/__ID__/sedes',
        csrfToken: '{{ csrf_token() }}',
    };
</script>
<script src="{{ asset('assets/js/instituciones.js') }}"></script>
@endpush
