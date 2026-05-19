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
                            <th>Identificador</th>
                            <th>Nombre</th>
                            <th>Dominios</th>
                            <th>Base de datos</th>
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
                    <label for="crear_id" class="form-label">Identificador</label>
                    <input type="text" class="form-control" id="crear_id" name="id" placeholder="Ej: unifranz">
                    <div class="form-text mb-3">Usa solo letras minusculas, numeros y guion bajo. Se usara para crear la base de datos.</div>
                    <div class="invalid-feedback" id="error_crear_id"></div>

                    <label for="crear_nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="crear_nombre" name="nombre" placeholder="Ej: Unifranz">
                    <div class="invalid-feedback" id="error_crear_nombre"></div>

                    <label for="crear_domain" class="form-label mt-3">Dominio</label>
                    <input type="text" class="form-control" id="crear_domain" name="domain" placeholder="Ej: unifranz.agenda.test">
                    <div class="invalid-feedback" id="error_crear_domain"></div>
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
                    <label for="editar_tenant_id" class="form-label">Identificador</label>
                    <input type="text" class="form-control" id="editar_tenant_id" disabled>
                    <div class="form-text mb-3">El identificador no se edita porque define el nombre de la base tenant.</div>

                    <label for="editar_nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="editar_nombre" name="nombre">
                    <div class="invalid-feedback" id="error_editar_nombre"></div>

                    <label for="editar_domain" class="form-label mt-3">Dominio</label>
                    <input type="text" class="form-control" id="editar_domain" name="domain">
                    <div class="invalid-feedback" id="error_editar_domain"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCrearAdministrador" tabindex="-1" aria-labelledby="modalCrearAdministradorLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formCrearAdministrador">
                @csrf
                <input type="hidden" id="administrador_tenant_id">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalCrearAdministradorLabel">Crear Administrador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <label for="administrador_institucion" class="form-label">Institucion</label>
                    <input type="text" class="form-control" id="administrador_institucion" disabled>
                    <div class="form-text mb-3">El usuario se creara dentro de la base de datos de esta institucion.</div>

                    <label for="administrador_name" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="administrador_name" name="name" placeholder="Ej: Administrador Unifranz">
                    <div class="invalid-feedback" id="error_admin_name"></div>

                    <label for="administrador_email" class="form-label mt-3">Correo</label>
                    <input type="email" class="form-control" id="administrador_email" name="email" placeholder="Ej: admin@unifranz.test">
                    <div class="invalid-feedback" id="error_admin_email"></div>

                    <label for="administrador_password" class="form-label mt-3">Contrasena</label>
                    <input type="password" class="form-control" id="administrador_password" name="password">
                    <div class="invalid-feedback" id="error_admin_password"></div>

                    <label for="administrador_password_confirmation" class="form-label mt-3">Confirmar contrasena</label>
                    <input type="password" class="form-control" id="administrador_password_confirmation" name="password_confirmation">
                    <div class="invalid-feedback" id="error_admin_password_confirmation"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Crear administrador</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const institucionesBaseUrl = @json(url('/instituciones'));

    window.institucionesConfig = {
        dataUrl: `${institucionesBaseUrl}/data`,
        storeUrl: institucionesBaseUrl,
        showUrlTemplate: `${institucionesBaseUrl}/__ID__`,
        updateUrlTemplate: `${institucionesBaseUrl}/__ID__`,
        destroyUrlTemplate: `${institucionesBaseUrl}/__ID__`,
        connectUrlTemplate: `${institucionesBaseUrl}/__ID__/conectar`,
        adminStoreUrlTemplate: `${institucionesBaseUrl}/__ID__/administrador`,
        csrfToken: @json(csrf_token()),
    };
</script>
<script src="{{ asset('assets/js/instituciones.js') }}"></script>
@endpush
